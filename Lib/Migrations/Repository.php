<?php
/**
 * Freyja Database Migration Repository.
 *
 * @package Freyja\Database\Migrations
 * @copyright 2016 SqueezyWeb
 * @since 0.3.0
 */

namespace Freyja\Database\Migrations;

use Freyja\Database\Database;
use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\RuntimeException;
use Freyja\Exceptions\ExceptionInterface;
use Freyja\Database\Schema\Schema;
use Freyja\Database\Schema\Table;
use Freyja\Database\Schema\Field;

/**
 * Database Migration Repository.
 *
 * @package Freyja\Database\Migrations
 * @author Mattia Migliorini <mattia@squeezyweb.com>
 * @since 0.3.0
 * @version 1.0.0
 */
class Repository implements RepositoryInterface {
	/**
	 * Database instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Database
	 */
	protected $database;

	/**
	 * Migration table name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $table;

	/**
	 * Query class to use.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $query;

	/**
	 * Create new database migration repository instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Database $db Database instance.
	 * @param string $table Database table name.
	 */
	public function __construct(Database $db, $table) {
		if (!is_string($table))
			throw InvalidArgumentException::typeMismatch('table', $table, 'String');

		$this->database = $db;
		$this->table = $table;
		$this->query = 'Freyja\Database\Query\\'.str_replace('Driver', '', $this->database->getDriver()).'Query';
	}

	/**
	 * Retrieve ran migrations.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 *
	 * @throws RuntimeException if the query fails.
	 */
	public function getRan() {
		try {
			$result = $this->database->execute(
				$this->getQuery()
					->select('batch')
					->orderBy('batch', 'ASC')
					->orderBy('migration', 'ASC')
			)->get();
		} catch (ExceptionInterface $e) {
			throw $e;
		}

		if (!is_array($result))
			throw new RuntimeException('Unexpected query result');

		$migrations = array();
		foreach ($result as $r)
			$migrations[] = $r['batch'];

		return $migrations;
	}

	/**
	 * Retrieve last migration.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function getLast() {
		try {
			return $this->database->execute(
				$this->getQuery()
					->select('batch', 'migration')
					->orderBy('batch', 'DESC')
					->orderBy('migration', 'DESC')
			)->first();
		} catch (ExceptionInterface $e) {
			throw $e;
		}
	}

	/**
	 * Log that migration was run.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $file Migration file.
	 * @param int $batch Migration batch.
	 */
	public function log($file, $batch) {
		if (!is_string($file))
			throw InvalidArgumentException::typeMismatch('file', $file, 'String');
		if (!is_numeric($batch))
			throw InvalidArgumentException::typeMismatch('batch', $batch, 'String');


		try {
			$query = $this->getQuery()->insert(
				array('migration', 'batch'),
				array(array($file, $batch))
			);
			$this->database->execute($query);
		} catch (ExceptionInterface $e) {
			throw $e;
		}
	}

	/**
	 * Remove migration from log.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param MigrationInterface $migration
	 */
	public function delete(MigrationInterface $migration) {
		try {
			$this->database->execute(
				$this->getQuery()
					->where('migration', $migration->migration)
					->delete()
			);
		} catch (ExceptionInterface $e) {
			throw $e;
		}
	}

	/**
	 * Retrieve last migration batch number.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int Last migration batch number.
	 */
	public function getLastBatchNumber() {
		try {
			$row = $this->database->execute(
				$this->getQuery()->select('max(batch) AS batch')
			)->first();
      return $row['batch'];
		} catch (ExceptionInterface $e) {
			throw $e;
		}
	}

	/**
	 * Create migration repository data store.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function createRepository() {
		try {
			$schema = new Schema($this->database);
      $batch = new Field('batch');
      $migration = new Field('migration');
			$schema->create(
				new Table(
					$this->table,
					array(
						$batch->timestamp()->notNull(),
						$migration->varchar()->notNull()
					)
				)
			);
		} catch (ExceptionInterface $e) {
			throw $e;
		}
	}

	/**
	 * Determine if the migration repository exists.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function repositoryExists() {
		$schema = new Schema($this->database);
		return $schema->hasTable($this->table);
	}

	/**
	 * Retrieve database associated with this repository.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Database
	 */
	public function getDatabase() {
		return $this->database;
	}

	/**
	 * Retrieve query object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return Query\QueryInterface
	 */
	protected function getQuery() {
		$query = new $this->query;
		return $query->table($this->table);
	}
}
