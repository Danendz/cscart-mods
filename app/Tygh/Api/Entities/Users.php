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
use Tygh\Enum\UserTypes;
use Tygh\Registry;

class Users extends AEntity
{
    /**
     * Gets user data for a specified id; if no id specified, gets user list
     * satisfying filter conditions specified  in params
     *
     * @param  int   $id     User identifier
     * @param  array $params Filter params (user_ids param ignored on getting one user)
     * @return mixed
     */
    public function index($id = 0, $params = array())
    {
        if (!$this->safeGet($params, 'user_type', '') && empty($id)) {
            return [
                'status' => Response::STATUS_BAD_REQUEST,
                'data' => __('api_required_field', [
                    '[field]' => 'user_type'
                ])
            ];
        }

        if (!empty($id)) {
            $params['user_id'] = $id;
        } elseif (!empty($params['user_ids']) && is_array($params['user_ids'])) {
            $params['user_id'] = $params['user_ids'];
        }

        $auth = $this->auth;
        $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_elements_per_page'));

        list($data, $params) = fn_get_users($params, $auth, $items_per_page);

        if ($id && !empty($data)) {
            $data = reset($data);
        } else {
            $data = array(
                'users' => $data,
                'params' => $params,
            );
        }

        if (!empty($data) || !empty($id)) {
            $status = Response::STATUS_OK;
        } else {
            $status = Response::STATUS_NOT_FOUND;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();
        $valid_params = true;

        $auth = $this->auth;

        $params = $this->filterUserData($params);

        $user_id = 0;

        if (empty($params['email'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'email'
            ));
            $valid_params = false;
        }

        if (empty($params['user_type'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'user_type'
            ));
            $valid_params = false;
        }

        if (!isset($params['company_id'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'company_id'
            ));
            $valid_params = false;
        }

        if (
            fn_allowed_for('MULTIVENDOR:ULTIMATE')
            && UserTypes::isAdmin($params['user_type'])
        ) {
            $params['storefront_id'] = 0;
        }

        if ($valid_params) {
            list($user_id, $profile_id) = fn_update_user($user_id, $params, $auth, false, false);

            if ($user_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'user_id' => $user_id,
                    'profile_id' => $profile_id
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $auth = $this->auth;

        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        $params = $this->filterUserData($params);

        list($user_id, $profile_id) = fn_update_user($id, $params, $auth, true, false);
        if ($user_id) {
            $status = Response::STATUS_OK;
            $data = array(
                'user_id' => $user_id,
                'profile_id' => $profile_id
            );
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        if (fn_delete_user($id)) {
            $status = Response::STATUS_NO_CONTENT;
        } elseif (!fn_notification_exists('extra', 'user_delete_no_permissions')) {
            $status = Response::STATUS_NOT_FOUND;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'manage_users',
            'update' => 'manage_users',
            'delete' => 'manage_users',
            'index'  => 'view_users'
        );
    }

    public function childEntities()
    {
        return array(
            'usergroups',
        );
    }


    /**
     * Filters user data.
     *
     * @param array $user_data
     *
     * @return array
     */
    protected function filterUserData($user_data)
    {
        unset($user_data['user_id'], $user_data['salt']);

        if (isset($user_data['password'])) {
            $user_data['password1'] = $user_data['password2'] = $user_data['password'];
            unset($user_data['password']);
        }

        return $user_data;
    }
}
