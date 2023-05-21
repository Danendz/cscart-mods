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

namespace Tygh\Addons\TwoFactorAuth\Users;

use Tygh\Addons\TwoFactorAuth\Enum\TwoFactorAuthTypes;
use Tygh\Addons\TwoFactorAuth\ServiceProvider;
use Tygh\Application;
use Tygh\Enum\SiteArea;
use Tygh\Mailer\Mailer;

/**
 * Class Users TFAuthFunctions
 *
 * @package TFAuthFunctions
 */
class TFAuthFunctions
{
    protected TFAuthEntity $tf_auth;
    protected TFAuthConstants $tf_auth_constants;
    protected Mailer $mailer;

    /**
     * TFAuth constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->tf_auth = ServiceProvider::getTFAuthEntity();
        $this->tf_auth_constants = ServiceProvider::getTFAuthConstants();
        $this->mailer = $app['mailer'];
    }

    /**
     * Counts the time since the last email sent
     *
     * @return array Returns an array that contains $interval_minutes and $interval_seconds
     */
    public function getEmailCodeInterval()
    {
        $sent_at = $this->tf_auth->get('sent_at');

        $now = time();

        $sent_at = strtotime(
            '+' . $this->tf_auth_constants->getIntervalBetweenEmailCodesInMinutes()
                . ' minutes',
            $sent_at
        );

        $interval = $sent_at - $now;
        $interval_seconds = $interval % 60;
        $interval_minutes = (int) ($interval / 60);

        return [$interval_minutes, $interval_seconds];
    }

    /**
     * Generates new code and send it via email
     *
     * @return bool Is email sent successfully
     */
    public function sendEmailCode()
    {
        $email_code = '';
        $email_code = str_replace('-', '', fn_generate_code(
            '',
            $this->tf_auth_constants->getMaxCodeLength()
        ));

        $user_email = $this->tf_auth->get('user_email');

        if (empty($user_email)) {
            return false;
        }

        $is_email_sent = $this->sendEmail($user_email, $email_code);

        if ($is_email_sent === false) {
            return false;
        }

        $this->tf_auth->set('code', $email_code);
        $this->tf_auth->set('sent_at', time());

        $expires_at = strtotime(
            '+' . $this->tf_auth_constants->getMaxEmailLifeTimeInMinutes() . 'minutes'
        );

        $this->tf_auth->set('expires_at', $expires_at);

        return true;
    }

    /**
     * Login user and unset tf_auth params
     *
     * @return void
     */
    public function loginUser()
    {
        $this->tf_auth->setIsVerified(TwoFactorAuthTypes::VERIFIED);
        $user_id = $this->tf_auth->get('user_id');
        fn_login_user($user_id, true);
        $this->tf_auth->unset();
    }

    /**
     * Send email with code
     *
     * @param $to Send email to
     * @param $email_code Generated email code
     *
     * @return bool Is email sent successfully
     */
    protected function sendEmail($to, $email_code)
    {
        $from = 'default_company_support_department';

        return $this->mailer->send(
            [
                'to' => $to,
                'from' => $from,
                'reply_to' => 'company_users_department',
                'data' => [
                    'email_code' => $email_code
                ],
                'tpl' => 'addons/two_factor_auth/confirm_code.tpl'
            ],
            SiteArea::STOREFRONT,
            CART_LANGUAGE
        );
    }
}
