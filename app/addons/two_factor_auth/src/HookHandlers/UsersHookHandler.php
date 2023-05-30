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

namespace Tygh\Addons\TwoFactorAuth\HookHandlers;

use Tygh\Application;
use Tygh\Tygh;
use Tygh\Addons\TwoFactorAuth\Enum\TwoFactorAuthTypes;
use Tygh\Addons\TwoFactorAuth\ServiceProvider;

/**
 * This class describes the hook handlers related to users
 *
 * @package Tygh\Addons\TwoFactorAuth\HookHandlers
 */
class UsersHookHandler
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * The "login_user_pre" hook handler
     *
     * Actions performed:
     *  - Redirect to two factor auth page
     *
     * @see fn_login_user
     */
    public function onLoginUserPre($user_id)
    {
        $tf_auth = ServiceProvider::getTFAuthEntity();
        $is_verified = $tf_auth->getIsVerified();
        if (!empty($_POST) && $is_verified !== TwoFactorAuthTypes::VERIFIED) {
            $tf_auth->unset();
            $tf_auth->set('user_id', $user_id);
            $tf_auth->set('user_email', $_REQUEST['user_login'] ?? '');
            $tf_auth->set('return_url', $_REQUEST['return_url'] ?? '');

            $tf_auth->setIsVerified(TwoFactorAuthTypes::NOT_VERIFIED);

            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('force_redirection', fn_url('two_factor_auth.verify'));
            } else {
                fn_redirect('two_factor_auth.verify');
            }

            exit;
        }
    }
}
