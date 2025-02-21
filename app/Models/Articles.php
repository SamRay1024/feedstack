<?php declare(strict_types=1);

namespace App\Models;

use RuntimeException;
use wlib\Db\Table;

class Articles extends Table
{
	const TABLE_NAME = 'articles';
	const COL_ID_NAME = 'id';
	const COL_CREATED_AT_NAME = 'created_at';
	const COL_UPDATED_AT_NAME = 'updated_at';
	const COL_DELETED_AT_NAME = 'deleted_at';

	protected $aColumns = [
		'feed_id'	=> \PDO::PARAM_INT,
		'is_new'	=> \PDO::PARAM_BOOL,
		'is_read'	=> \PDO::PARAM_BOOL
	];

	public function filterFields(array $aFields, $id = 0): array
	{
		$aFiltered = filter_var_array($aFields, [
			'feed_id'		=> FILTER_VALIDATE_INT,
			'title'			=> $this->getFilter('sanitize_string'),
			'link'			=> FILTER_VALIDATE_URL,
			'author'		=> $this->getFilter('sanitize_string'),
			'category'		=> $this->getFilter('sanitize_string'),
			'summary'		=> FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'content'		=> FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'content_md5'	=> $this->getFilter('validate_string_alnum'),
			'pub_date'		=> $this->getFilter('sanitize_string'),
			'is_new'		=> FILTER_VALIDATE_BOOL,
			'is_read'		=> FILTER_VALIDATE_BOOL,
			'is_archive'	=> FILTER_VALIDATE_BOOL,
			'is_read_later'	=> FILTER_VALIDATE_BOOL
		], false);
		
		if ($aFiltered === false)
			throw new RuntimeException(
				static::class.' : an error occured while filtering fields.'
				.' Please check your filters.'
			);

		// if (in_array(false, $aFiltered, true))
		// 	throw new UnexpectedValueException(sprintf(
		// 		static::class .' : unexpected value(s) for field(s) "%s".',
		// 		implode(', ', array_keys($aFiltered, false, true))
		// 	), 400);

		// if (isset($aFiltered['url']) && $this->exists('url', $aFiltered['url'], $id))
		// 	throw new UnexpectedValueException(sprintf(
		// 		static::class .' : Feed "%s" already added.',
		// 		$aFiltered['url']
		// 	), 409);

		if (isset($aFiltered['is_read']) && $aFiltered['is_read'])
			$aFiltered['is_new'] = 0;

		if (isset($aFiltered['is_archive']) && $aFiltered['is_archive'])
		{
			$aFiltered['is_new'] = 0;
			$aFiltered['is_read_later'] = 0;
		}

		return $aFiltered;
	}

	/**
	 * Reset "is_new" status for all articles of the given feed.
	 *
	 * @param integer $iFeedId Feed ID.
	 * @return void
	 */
	public function resetIsNew(int $iFeedId): void
	{
		$this->oDb->query()
			->update(self::TABLE_NAME)
			->set('is_new', 0, \PDO::PARAM_INT)
			->where('feed_id = :id AND '. self::COL_DELETED_AT_NAME .' IS NULL')
			->setParameter('id', $iFeedId)
			->run();
	}
	
	/**
	 * Count unread articles for the given feed.
	 *
	 * @param int $iFeedId
	 * @return int
	 */
	public function getUnreadArticlesCount(int $iFeedId): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME .' IS NULL AND '
				.'feed_id = :id AND is_read = 0'
			)
			->setParameter('id', $iFeedId)
			->run()->fetchColumn();
	}
	
	/**
	 * Count new articles for the given feed.
	 *
	 * @param int $iFeedId Feed ID.
	 * @return int
	 */
	public function getNewArticlesCount(int $iFeedId): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME .' IS NULL AND '
				.'feed_id = :id AND is_new = 1'
			)
			->setParameter('id', $iFeedId)
			->run()
			->fetchColumn();
	}

	/**
	 * Count today articles.
	 * 
	 * @param bool $bUnreadOnly Count unread articles only.
	 * @return int
	 */
	public function getTodayCount(bool $bUnreadOnly = false): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME.' IS NULL AND '
				.'is_archive = 0 AND created_at >= :created_at'
				.($bUnreadOnly ? ' AND is_read = 0' : '')
			)
			->setParameter('created_at', date('Y-m-d') .' 00:00:00')
			->run()->fetchColumn();
	}
	
	/**
	 * Count read later articles.
	 * 
	 * @param bool $bUnreadOnly Count unread articles only.
	 * @return int
	 */
	public function getReadLaterCount(bool $bUnreadOnly = false): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME.' IS NULL AND '
				.'is_archive = 0 AND is_read_later = 1'
				.($bUnreadOnly ? ' AND is_read = 0' : '')
			)
			->run()->fetchColumn();
	}

	/**
	 * Count archives.
	 * 
	 * @return int
	 */
	public function getArchivesCount(): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME.' IS NULL'
				.' AND is_archive = 1'
			)
			->run()->fetchColumn();
	}

	/**
	 * Make a query to count on current table.
	 * 
	 * @return \wlib\Db\Query
	 */
	private function makeQueryCount()
	{
		return $this->oDb->query()
			->select('COUNT('. self::COL_ID_NAME .')')
			->from(self::TABLE_NAME);
	}
}