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

namespace Tygh\Addons\TwoFactorAuth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\TwoFactorAuth\HookHandlers\UsersHookHandler;
use Tygh\Addons\TwoFactorAuth\Users\TFAuthConstants;
use Tygh\Addons\TwoFactorAuth\Users\TFAuthEntity;
use Tygh\Addons\TwoFactorAuth\Users\TFAuthFunctions;
use Tygh\Tygh;

/**
 * Class ServiceProvider is intended to register services and components of the two_factor_auth
 * add-on to the application container
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.two_factor_auth.users.tf_auth_entity'] = function (Container $app) {
            return new TFAuthEntity($app);
        };

        $app['addons.two_factor_auth.users.tf_auth_functions'] = function (Container $app) {
            return new TFAuthFunctions($app);
        };

        $app['addons.two_factor_auth.users.tf_auth_constants'] = function () {
            return new TFAuthConstants();
        };

        $app['addons.two_factor_auth.hook_handlers.users'] = function (Container $app) {
            return new UsersHookHandler($app);
        };
    }

    /**
     * @return TFAuthEntity
     */
    public static function getTFAuthEntity()
    {
        return Tygh::$app['addons.two_factor_auth.users.tf_auth_entity'];
    }

    /**
     * @return TFAuthFunctions
     */
    public static function getTFAuthFunctions()
    {
        return Tygh::$app['addons.two_factor_auth.users.tf_auth_functions'];
    }

    /**
     * @return TFAuthConstants
     */
    public static function getTFAuthConstants()
    {
        return Tygh::$app['addons.two_factor_auth.users.tf_auth_constants'];
    }
}
