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

use Tygh\Registry;
use Tygh\Enum\Addons\AgeRestriction\AgeRestrictionTypes;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/**
 * Compare allowed date with passed date and return AgeRestrictionType
 *
 * @param string $date Age from datepicker
 *
 * @return string|false returns false if date is invalid otherwise ALLOWED or DENIED string
 */
function fn_age_restriction_compare($date)
{
    $min_age = Registry::get('addons.age_restriction.min_age');
    $date = strtotime($date);
    if (empty($date)) {
        return false;
    }
    $allowedYearFrom = (int) date('Y') - $min_age;
    $specifiedYear = (int) date('Y', $date);
    return $specifiedYear <= $allowedYearFrom ?
        AgeRestrictionTypes::ALLOWED :
        AgeRestrictionTypes::DENIED;
}

/**
 * Redirect to home page if current page is not home page
 *
 * @return void
 */
function fn_age_restriction_redirect_home_page()
{
    $current_url = Registry::get('config.current_url');
    $home_url = fn_url();
    if (!fn_compare_dispatch($current_url, $home_url)) {
        fn_redirect($home_url);
        exit;
    }
}
