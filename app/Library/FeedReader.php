<?php declare(strict_types=1);

namespace App\Library;

use SimpleXMLElement;
use UnexpectedValueException;
use wlib\Http\Client\Http;

class FeedReader
{
	const TYPE_ATOM = 'Atom';
	const TYPE_RSS = 'Rss';

	private string $sUrl = '';
	private ?SimpleXMLElement $xml = null;
	private string $sType = '';

	public function __construct(string $sUrl)
	{
		if (!filter_var($sUrl, FILTER_VALIDATE_URL))
			throw new UnexpectedValueException('"'.$sUrl.'" is not a valid URL.');

		$this->sUrl = $sUrl;
	}

	public function check()
	{
		if (!$this->xml && !$this->load())
			return false;

		return ($this->sType == self::TYPE_ATOM
			? [
				'title' => (string) $this->xml->title,
				'lastUpdate' => (string) $this->xml->updated,
				'items' => count($this->xml->entry)
			]
			: [
				'title' => (string) $this->xml->channel->title,
				'lastUpdate' => (string) $this->xml->channel->lastBuildDate,
				'items' => count($this->xml->channel->item)
			]
		);
	}

	public function fetch()
	{
		if (!$this->xml && !$this->load())
			return false;

		switch ($this->sType)
		{
			case self::TYPE_ATOM: return $this->fetchItemsFromAtom();
			case self::TYPE_RSS: return $this->fetchItemsFromRss();
		}
	}

	private function load()
	{
		$aResponse = Http::get($this->sUrl);

		if ($aResponse['status_code'] != 200)
			return false;

		libxml_use_internal_errors(true);

		try
		{
			$this->xml = new SimpleXMLElement(
				$aResponse['body'],
				LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA
			);
		}
		catch (\Exception $ex) { return false; }

		if ($this->xml->updated)
			$this->sType = self::TYPE_ATOM;
		elseif ($this->xml->channel)
			$this->sType = self::TYPE_RSS;
		else
			throw new UnexpectedValueException('"'. $this->sUrl .'" feed type is not supported.');

		return true;
	}

	private function fetchItemsFromRss()
	{
		$aItems = [];

		/** @var SimpleXmlElement $item */
		foreach ($this->xml->channel->item as $item)
		{
			$aNS = $item->getNamespaces(true);
			
			$sContent = (string) $item->description;
			$sSummary = '';

			if (isset($aNS['content']))
			{
				$sSummary = $sContent;
				$sContent = (string) $item->children($aNS['content']);
			}

			if (isset($aNS['dc']))
				$dc = $item->children($aNS['dc']);

			$aItems[] = (object) [
				'title'		=> (string) $item->title ?? '',
				'author'	=> (isset($dc) ? (string) $dc->creator : ''),
				'link'		=> (string) $item->link ?? '',
				'pub_date'	=> strtotime((string) (
					$item->pubDate ?? (isset($dc) && $dc->date ? $dc->date : '')
				)),
				'category'	=> (string) $item->category ?? '',
				'summary'	=> $sSummary,
				'content'	=> $sContent
			];
		}

		return $aItems;
	}

	private function fetchItemsFromAtom()
	{
		$aItems = [];
		$sFeedAuthor = '';

		if ($this->xml->author && $this->xml->author->name)
			$sFeedAuthor = (string) $this->xml->author->name;

		/** @var SimpleXmlElement $item */
		foreach ($this->xml->entry as $item)
		{
			$sSummary = (string) $item->summary;
			$sContent = (string) $item->content;

			if ($sContent == '')
			{
				$sContent = $sSummary;
				$sSummary = '';
			}

			$aItems[] = (object) [
				'title'		=> (string) $item->title ?? '',
				'author'	=> ($item->author && $item->author->name
					? (string) $item->author->name
					: $sFeedAuthor
				),
				'link'		=> (string) $item->link['href'] ?? '',
				'pub_date'	=> strtotime((string) $item->updated ?? ''),
				'category'	=> (string) $item->category ?? '',
				'summary'	=> $sSummary,
				'content'	=> $sContent
			];
		}

		return $aItems;
	}
}