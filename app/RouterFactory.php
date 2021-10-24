<?php declare(strict_types = 1);

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;

class RouterFactory
{

	use Nette\StaticClass;

	public static function createRouter(): Nette\Routing\Router
	{
		$router = new RouteList();

		$router->withModule('Admin')
			->addRoute('admin/<presenter>/<action>[/<id>]', 'Homepage:default');

		$router->withModule('Front')->addRoute('[<lang=en en|ar|cs>/]', 'Homepage:default');

		$router->withModule('Front')->addRoute('[<lang=en en|ar|cs>/]contact', 'Homepage:contact');

		$router->withModule('Front')->addRoute('[<lang=en en|ar|cs>/]services', 'Service:default');

		$router->withModule('Front')->addRoute('[<lang=en en|ar|cs>/]service/<slug>', 'Service:detail');

		$router->withModule('Front')->addRoute('[<lang=en [a-z]{2}>/]<presenter>/<action>', 'Error:404');

		return $router;
	}

}
