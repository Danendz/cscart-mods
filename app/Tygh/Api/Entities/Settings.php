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

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Settings as CartSettings;
use Tygh\Registry;
use Tygh\Tygh;

class Settings extends AEntity
{
    /**
     * Gets settings list
     *
     * @param  int   $id     Settings identifier
     * @param  array $params Filter params
     *
     * @return array
     */
    public function index($id = 0, $params = [])
    {
        $company_id = $this->safeGet($params, 'company_id', null);
        $lang_code = $this->getLanguageCode($params);

        if (!empty($id)) {
            $data = CartSettings::instance()->getData($id, $company_id);

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $status = Response::STATUS_OK;
            }

        } else {
            $section_id = $this->safeGet($params, 'section_id', 0);
            $section_tab_id = $this->safeGet($params, 'section_tab_id', 0);
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_elements_per_page'));
            $page = $this->safeGet($params, 'page', 1);

            $storefront_id = null;
            $auth = Tygh::$app['session']['auth'];
            if (
                fn_allowed_for('MULTIVENDOR:ULTIMATE')
                && !empty($auth['storefront_id'])
            ) {
                $storefront_id = $auth['storefront_id'];
            }

            $data = CartSettings::instance()->getList($section_id, $section_tab_id, true, $company_id, $lang_code, $storefront_id);
            $total_items_count = count($data);

            if ($items_per_page) {
                $data = array_slice($data, ($page - 1) * $items_per_page, $items_per_page);
            }

            $data = [
                'settings' => $data,
                'params'   => [
                    'section_id'     => $section_id,
                    'section_tab_id' => $section_tab_id,
                    'items_per_page' => $items_per_page,
                    'page'           => $page,
                    'total_items'    => $total_items_count,
                ],
            ];
            $status = Response::STATUS_OK;
        }

        return [
            'status' => $status,
            'data'   => $data,
        ];
    }

    public function create($params)
    {
        return [
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        ];
    }

    public function update($id, $params)
    {
        $data = [];
        $status = Response::STATUS_BAD_REQUEST;

        if (!empty($id) && array_key_exists('value', $params)) {
            $company_id = $this->safeGet($params, 'company_id', null);

            $result = CartSettings::instance()->updateValueById($id, $params['value'], $company_id);

            if ($result) {
                $status = Response::STATUS_OK;
                $data = [
                    'setting_id' => $id
                ];
            }
        }

        return [
            'status' => $status,
            'data'   => $data
        ];
    }

    public function delete($id)
    {
        return [
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        ];
    }

    public function privileges()
    {
        return [
            'create' => 'update_settings',
            'update' => 'update_settings',
            'delete' => 'update_settings',
            'index'  => 'view_settings'
        ];
    }
}
