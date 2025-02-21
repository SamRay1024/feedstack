<?php

namespace App\Controllers;

use App\Library\FeedReader;
use wlib\Application\Controllers\Controller;

class TestController extends Controller
{
	public function start()
	{
		$fr = new FeedReader('https://jebulle.net/feed');
		$items = $fr->fetch();

		vdd(strip_tags(htmlspecialchars_decode($items[0]->content, ENT_QUOTES)));
	}
}