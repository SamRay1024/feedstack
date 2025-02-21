<?php

namespace App\Controllers;

use wlib\Application\Controllers\WebGateController;

class AuthController extends WebGateController
{
	protected function stylesheets(): array
	{
		return [
			['href' => 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.36.0/tabler-icons.min.css'],
			['href' => '/assets/css/w.css?'.time(), 'media' => 'screen'],
			['href' => '/assets/css/wrss.css?'.time(), 'media' => 'screen']
		];
	}
}