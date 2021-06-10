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
        $router->addRoute('post/edit/<wall_post_id>', 'Post:edit');
        $router->addRoute('post/delete', 'Post:delete');
        $router->addRoute('post/hide', 'Post:hide');
        $router->addRoute('post/publish', 'Post:publish');
        $router->addRoute('post[/<wall_post_id>]', 'Post:show');

        /**
         * game routes
         */
        $router->addRoute('games', 'Games:default');
        $router->addRoute('games/create', 'Games:create');
        $router->addRoute('games/edit/<game_id>', 'Games:edit');
        $router->addRoute('games[/<game_id>]', 'Games:detail');

        /**
         * messages routes
         */
        $router->addRoute('messages/<action>/<to_user_id>', 'Messages:default');
        $router->addRoute('messages/<inbox_hash>', 'Messages:default');


        /**
         * default route
         */
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}
}
