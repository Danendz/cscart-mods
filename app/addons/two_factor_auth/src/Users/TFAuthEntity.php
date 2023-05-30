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

use Tygh\Application;
use Tygh\Tygh;

/**
 * Class Users TFAuthEntity
 *
 * @package TFAuthEntity
 */
class TFAuthEntity
{
    /**
     * TFAuthEntity constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $tf_auth = &Tygh::$app['session']['tf_auth'];
        if (empty($tf_auth)) {
            $tf_auth = [];
        }
    }

    /**
     * Get tf_auth value by key
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Tygh::$app['session']['tf_auth'][$key] ?? '';
    }

    /**
     * Set value to tf_auth by key
     *
     * @param string $key Key
     * @param mixed $value Value
     *
     * @return void
     */
    public function set($key, $value)
    {
        Tygh::$app['session']['tf_auth'][$key] = $value;
    }

    /**
     * Unset tf_auth
     *
     * @return void
     */
    public function unset()
    {
        unset(Tygh::$app['session']['tf_auth']);
    }

    /**
     * Gets is_verified value from session auth
     *
     * @return string tf_auth state or null
     */
    public function getIsVerified()
    {
        return Tygh::$app['session']['auth']['is_verified'] ?? '';
    }

    /**
     * Sets is_verified for session auth
     *
     * @param string $value Verified state
     *
     * @return void
     */
    public function setIsVerified($value)
    {
        Tygh::$app['session']['auth']['is_verified'] = $value;
    }
}
