<?php

/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Addons\TwoFactorAuth\ServiceProvider;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\UserTypes;
use Tygh\Tygh;

$tf_auth = ServiceProvider::getTFAuthEntity();
$tf_auth_functions = ServiceProvider::getTFAuthFunctions();
$tf_auth_constants = ServiceProvider::getTFAuthConstants();
$verify_url = fn_url('two_factor_auth.verify');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'send_code') {
        $can_send_email = true;
        $interval_minutes = 0;
        $interval_seconds = 0;
        $code = $tf_auth->get('code');

        if (!empty($code)) {
            [$interval_minutes, $interval_seconds] = $tf_auth_functions->getEmailCodeInterval();
            $can_send_email = $interval_seconds <= 0;
        }

        if ($can_send_email) {
            $is_sent_successfully = $tf_auth_functions->sendEmailCode();

            if ($is_sent_successfully === true) {
                fn_set_notification(
                    NotificationSeverity::NOTICE,
                    __('notice'),
                    __('two_factor_auth_success_code_send')
                );
            } else {
                fn_set_notification(
                    NotificationSeverity::ERROR,
                    __('error'),
                    __('two_factor_auth_unable_send_email')
                );
            }
        } else {
            fn_set_notification(
                NotificationSeverity::NOTICE,
                __('notice'),
                __('two_factor_auth_can_send_code_after') . ' ' .
                    "$interval_minutes " . __('two_factor_auth_minutes') . ' ' .
                    "$interval_seconds " . __('two_factor_auth_seconds')
            );
        }

        return [CONTROLLER_STATUS_OK, $verify_url];
    } elseif ($mode === 'confirm_code') {
        $verify_code = isset($_REQUEST['verify_code']) ? fn_strtolower($_REQUEST['verify_code']) : '';

        $attempts = $tf_auth->get('attempts');
        $expires_at = $tf_auth->get('expires_at');
        $code = $tf_auth->get('code');
        $return_url = fn_url($tf_auth->get('return_url'));

        if (empty($expires_at) && empty($code)) {
            fn_set_notification(
                NotificationSeverity::WARNING,
                __('warning'),
                __('two_factor_auth_not_send_code_yet')
            );
            return [CONTROLLER_STATUS_OK, $verify_url];
        }

        if (empty($verify_code)) {
            fn_set_notification(
                NotificationSeverity::NOTICE,
                __('notice'),
                __('two_factor_auth_empty_field')
            );
            return [CONTROLLER_STATUS_OK, $verify_url];
        }

        if (empty($attempts) && $attempts !== 0) {
            $attempts = $tf_auth_constants->getMaxAttempts();
            $tf_auth->set('attempts', $attempts);
        }

        if ($verify_code === strtolower($code)) {
            if ($expires_at < time()) {
                fn_set_notification(
                    NotificationSeverity::WARNING,
                    __('warning'),
                    __('two_factor_auth_code_expired')
                );
                return [CONTROLLER_STATUS_OK, $verify_url];
            }

            $tf_auth_functions->loginUser();

            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('force_redirection', $return_url);
            }

            return [CONTROLLER_STATUS_REDIRECT, $return_url];
        }

        $attempts -= 1;
        $tf_auth->set('attempts', $attempts);

        if ($attempts <= 0) {
            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('force_redirection', fn_url('auth.login_form'));
            }

            return [CONTROLLER_STATUS_REDIRECT, fn_url('auth.login_form')];
        }

        $invalid_code_text = __('two_factor_auth_invalid_code');
        $remain_attempts_text = __('two_factor_auth_remain_attempts');
        fn_set_notification(
            NotificationSeverity::WARNING,
            __('warning'),
            "$invalid_code_text. $remain_attempts_text: $attempts"
        );

        return [CONTROLLER_STATUS_OK, $verify_url];
    }
}

if ($mode === 'verify') {
    $user_id = $tf_auth->get('user_id');
    $user_email = $tf_auth->get('user_email');

    if (empty($user_id) || empty($user_email)) {
        return [CONTROLLER_STATUS_REDIRECT, fn_url()];
    }

    $user_info = fn_get_user_short_info($user_id);

    if ($user_info['user_type'] === UserTypes::ADMIN) {
        $tf_auth_functions->loginUser();
        $return_url = fn_url($tf_auth->get('return_url'));

        if (defined('AJAX_REQUEST')) {
            Tygh::$app['ajax']->assign('force_redirection', $return_url);
        }

        return [CONTROLLER_STATUS_REDIRECT, $return_url];
    }

    return [CONTROLLER_STATUS_OK];
}
