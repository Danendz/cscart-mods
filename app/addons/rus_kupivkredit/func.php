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

// rus_build_kupivkredit dbazhenov

use Tygh\Registry;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\SiteArea;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_actions_addons_rus_kupivkredit($new_status, $old_status, $on_install)
{
    if ($new_status == 'D') {
        $payment_ids = fn_rus_kupivkredit_disable_payments();
        if (!empty($payment_ids)) {
            fn_set_notification('W', __('warning'), __('kvk_payment_disabled'));
        }
    } else {
        $payment_ids = fn_rus_kupivkredit_get_payment_ids(true);
        if (!empty($payment_ids)) {
            fn_set_notification('W', __('warning'), __('kvk_has_disabled_payments', array(
                '[url]' => fn_url('payments.manage')
            )));
        }
    }
}

function fn_rus_kupivkredit_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    if (
        Registry::ifGet('addons.rus_kupivkredit.status', ObjectStatuses::DISABLED) !== ObjectStatuses::ACTIVE
        || Registry::get('runtime.action') !== 'kvk_activate'
    ) {
        return;
    }

    $params = [
        'lang_code' => CART_LANGUAGE,
    ];

    if ($auth['area'] === SiteArea::STOREFRONT) {
        $params['status'] = ObjectStatuses::ACTIVE;
        $params['usergroup_ids'] = $auth['usergroup_ids'];
    }

    $payment_methods = fn_get_payments($params);

    foreach ($payment_methods as $data) {
        if (empty($data['processor']) || !stristr($data['processor'], 'Тинькофф: Кредитование покупателей (КупиВкредит)')) {
            continue;
        }

        $cart['payment_id'] = $data['payment_id'];
    }
}

function fn_rus_kupivkredit_get_payments($params, &$fields, $join, $order, $condition, $having)
{
    $fields[] = '?:payment_processors.processor AS processor';
}

/**
 * The "get_payment_processors_post" hook handler.
 *
 * Actions performed:
 *     - Adds specific 'russian' attribute to some payment processors for categorization.
 *
 * @see \fn_get_payment_processors()
 */
function fn_rus_kupivkredit_get_payment_processors_post($lang_code, &$processors)
{
    foreach ($processors as &$processor) {
        if ($processor['addon'] === 'rus_kupivkredit') {
            $processor['russian'] = true;
        }
    }
}

function fn_rus_kupivkredit_install_payment()
{
    $processor_id = fn_rus_kupivkredit_get_processor_id();

    if (empty($processor_id)) {
        $payment_data = array(
            'processor' => 'Тинькофф: Кредитование покупателей (КупиВкредит)',
            'processor_script' => 'kupivkredit.php',
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template' => 'kupivkredit.tpl',
            'callback' => 'Y',
            'type' => 'P',
            'addon' => 'rus_kupivkredit',
        );

        db_query("REPLACE INTO ?:payment_processors ?e", $payment_data);
    }
}

function fn_rus_kupivkredit_delete_payment()
{
    fn_rus_kupivkredit_disable_payments(true);

    db_query("DELETE FROM ?:payment_processors WHERE processor_script = 'kupivkredit.php'");
}

function fn_rus_kupivkredit_disable_payments($drop_processor_id = false)
{
    $payment_ids = fn_rus_kupivkredit_get_payment_ids();

    if (!empty($payment_ids)) {
        foreach ($payment_ids as $payment_id) {
            $fields = '';
            if ($drop_processor_id) {
                $fields = 'processor_id = 0,';
            }
            db_query("UPDATE ?:payments SET $fields status = 'D' WHERE payment_id = ?i", $payment_id);
        }
    }

    return $payment_ids;
}

function fn_rus_kupivkredit_get_payment_ids($only_disabled = false)
{
    $fields = '';
    if ($only_disabled) {
        $fields = " AND status = 'D' ";
    }

    $processor_id = fn_rus_kupivkredit_get_processor_id();

    if (!empty($processor_id)) {
        $payment_ids = db_get_fields("SELECT payment_id FROM ?:payments WHERE processor_id = ?i $fields", $processor_id);
    } else {
        $payment_ids = array();
    }

    return $payment_ids;
}

function fn_rus_kupivkredit_get_processor_id()
{
    return db_get_field("SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'kupivkredit.php'");
}

/**
 * The "prepare_checkout_payment_methods" hook handler.
 * Actions performed:
 *      - Unset Kupi v kredit if cart total < 3000
 *
 * @param array<string, string|int|array>                      $cart           Cart content
 * @param array<string, string|int|array>                      $auth           Auth data
 * @param array<string, array<int, array<string, int|string>>> $payment_groups Payment groups
 *
 * @return void
 */
function fn_rus_kupivkredit_prepare_checkout_payment_methods(array $cart, array $auth, array &$payment_groups)
{
    if (!isset($cart['total']) || $cart['total'] >= 3000) {
        return;
    }

    foreach ($payment_groups as $payment_group => $payments) {
        foreach ($payments as $id => $payment) {
            if ($payment['processor_script'] !== 'kupivkredit.php') {
                continue;
            }

            unset($payment_groups[$payment_group][$id]);
        }
    }
}
