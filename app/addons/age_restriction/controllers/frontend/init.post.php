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
use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

$auth = Tygh::$app['session']['auth'];

if ($auth['user_id'] !== 0) {
    return;
}

$is_legit = fn_get_cookie(AGE_RESTRICTION_IS_LEGIT);
$min_age = '';

if (empty($is_legit)) {
    $is_legit = AgeRestrictionTypes::NOT_SET;
}

switch ($is_legit) {
    case AgeRestrictionTypes::ALLOWED:
        return;
    case AgeRestrictionTypes::DENIED:
        fn_age_restriction_redirect_home_page();
        break;
    case AgeRestrictionTypes::NOT_SET:
        $min_age = Registry::get('addons.age_restriction.min_age');
        break;
}

Tygh::$app['view']->assign([
    'is_legit' => $is_legit,
    'min_age' => $min_age
]);
