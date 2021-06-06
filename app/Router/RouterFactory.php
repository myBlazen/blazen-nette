<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

		/**
         * user routes
         */
		$router->addRoute('user/settings','User:settings');
        $router->addRoute('user/statistics','User:statistics');
        $router->addRoute('user[/<username>]', 'User:profile');

        /**
         * post routes
         */
        $router->addRoute('post/<wall_post_id>', 'Post:show');

        /**
         * game routes
         */
        $router->addRoute('games/<game_id>', 'Games:detail');

        /**
         * default route
         */
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}
}
