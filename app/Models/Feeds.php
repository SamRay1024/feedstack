<?php declare(strict_types=1);

namespace App\Models;

use RuntimeException;
use UnexpectedValueException;
use wlib\Application\Exceptions\UnexpectedFieldValueException;
use wlib\Db\Table;

class Feeds extends Table
{
	const TABLE_NAME = 'feeds';
	const COL_ID_NAME = 'id';
	const COL_CREATED_AT_NAME = 'created_at';
	const COL_UPDATED_AT_NAME = 'updated_at';

	public function filterFields(array $aFields, $id = 0): array
	{
		$aFiltered = filter_var_array($aFields, [
			'title'			=> FILTER_DEFAULT,
			'url'			=> FILTER_VALIDATE_URL,
			'last_update'	=> FILTER_SANITIZE_FULL_SPECIAL_CHARS
		], false);
		
		if (!count($aFiltered))
			throw new UnexpectedValueException(
				static::class .' : no data provided. Nothing to do.'
			);

		if ($aFiltered === false)
			throw new RuntimeException(
				static::class.' : an error occured while filtering fields.'
				.' Please check your filters.'
			);

		if (in_array(false, $aFiltered, true))
			throw new UnexpectedValueException(sprintf(
				static::class .' : unexpected value(s) for field(s) "%s".',
				implode(', ', array_keys($aFiltered, false, true))
			), 400);

		if (isset($aFiltered['url']) && $this->exists('url', $aFiltered['url'], $id))
			throw new UnexpectedFieldValueException('url', sprintf(
				static::class .' : Feed "%s" already added.',
				$aFiltered['url']
			), 409);

		if (isset($aFiltered['title']))
			$aFiltered['title'] = strip_tags($aFiltered['title']);

		return $aFiltered;
	}
}