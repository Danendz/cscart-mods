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

use Tygh\Enum\Addons\AgeRestriction\AgeRestrictionTypes;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'verify') {
        $is_legit = fn_get_cookie(AGE_RESTRICTION_IS_LEGIT);
        $is_cookie_set = $is_legit === AgeRestrictionTypes::ALLOWED || $is_legit === AgeRestrictionTypes::DENIED;
        if ($_REQUEST['age'] && !$is_cookie_set) {
            $is_age_valid = fn_age_restriction_compare($_REQUEST['age']);
            fn_set_cookie(AGE_RESTRICTION_IS_LEGIT, $is_age_valid);
        }

        //Redirect to redirect_url if exists
        return [CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url'] ?? ''];
    }
}
