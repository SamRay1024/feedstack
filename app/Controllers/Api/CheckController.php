<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Library\FeedReader;
use wlib\Application\Controllers\AllowLoggedInUsersTrait;
use wlib\Application\Controllers\Controller;

class CheckController extends Controller
{
	use AllowLoggedInUsersTrait;

	public function start()
	{
		if (!$this->hasParam('url'))
			$this->haltBadRequest('You must provide "url" parameter.');

		$sUrl = $this->param('url');

		if (!filter_var($sUrl, FILTER_VALIDATE_URL))
			$this->haltBadRequest('Please provide a valid "url".');

		$feed = new FeedReader($sUrl);

		$aData = $feed->check();

		$this->response->json(
			($aData
				? ['data' => $aData]
				: ['error' => [
					'source' => ['feed' => $sUrl],
					'title' => 'Unable to parse the given URL. Is it a feed ?'
				]]
			),
			($aData ? 200 : 400)
		);
	}
}