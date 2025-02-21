<?php declare(strict_types=1);

namespace App\Controllers;

use wlib\Application\Controllers\FrontController;
use wlib\Application\Controllers\RestrictedWebAreaTrait;

class IndexController extends FrontController
{
	use RestrictedWebAreaTrait;

	public function start()
	{
		$this->response->html($this->render('index', [
			'sHttpHost'		=> $this->request->getServer('HTTP_HOST'),
			'sLogoutUrl'	=> $this->app->get('guard.web')->getLogoutUrl()
		]));
	}
}