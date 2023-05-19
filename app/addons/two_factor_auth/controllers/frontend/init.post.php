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

use Tygh\Addons\TwoFactorAuth\Enum\TwoFactorAuthTypes;
use Tygh\Addons\TwoFactorAuth\ServiceProvider;
use Tygh\Registry;

$tf_auth = ServiceProvider::getTFAuthEntity();

$current_url = fn_url(Registry::get('config.current_url'));
$verification_url = fn_url('two_factor_auth.verify');

if (
    !fn_compare_dispatch($current_url, $verification_url)
    && $tf_auth->getIsVerified() === TwoFactorAuthTypes::NOT_VERIFIED
    && $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    $tf_auth->unset();
}
