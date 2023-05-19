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

use Tygh\Registry;

/**
 * Class Users TFAuthConstants
 *
 * @package TFAuthConstants
 */
class TFAuthConstants
{
    public function __construct()
    {
    }

    /**
     * Gets max attempts
     *
     * @return int max attempts
     */
    public function getMaxAttempts()
    {
        return Registry::get('addons.two_factor_auth.max_attempts') ?? 3;
    }

    /**
     * Gets max code length
     *
     * @return int max code length
     */
    public function getMaxCodeLength()
    {
        return Registry::get('addons.two_factor_auth.code_length') ?? 4;
    }

    /**
     * Gets interval between email codes in minutes
     *
     * @return int interval in minutes
     */
    public function getIntervalBetweenEmailCodesInMinutes()
    {
        return Registry::get('addons.two_factor_auth.interval_between_emails_in_minutes') ?? 1;
    }

    /**
     * Gets max email life time in minutes
     *
     * @return int life time in minutes
     */
    public function getMaxEmailLifeTimeInMinutes()
    {
        return Registry::get('addons.two_factor_auth.email_life_time_in_minutes') ?? 5;
    }
}
