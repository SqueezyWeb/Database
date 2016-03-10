<?php
/**
 * Freyja Database Migration Repository Interface.
 *
 * @package Freyja\Database\Migrations
 * @copyright 2016 SqueezyWeb
 * @since 0.3.0
 */

namespace Freyja\Database\Migrations;

/**
 * Database Migration Repository Interface.
 *
 * @package Freyja\Database\Migrations
 * @author Mattia Migliorini <mattia@squeezyweb.com>
 * @since 0.3.0
 * @version 1.0.0
 */
interface RepositoryInterface {
	/**
	 * Retrieve ran migrations.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function getRan();

	/**
	 * Retrieve last migration.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function getLast();

	/**
	 * Log that migration was run.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $file Migration file.
	 * @param int $batch Migration batch.
	 */
	public function log($file, $batch);

	/**
	 * Remove migration from log.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param MigrationInterface $migration
	 */
	public function delete(MigrationInterface $migration);

	/**
	 * Retrieve last migration batch number.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int Last migration batch number.
	 */
	public function getLastBatchNumber();

	/**
	 * Create migration repository data store.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function createRepository();

	/**
	 * Determine if the migration repository exists.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function repositoryExists();

	/**
	 * Retrieve database associated with this repository.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Database
	 */
	public function getDatabase();
}
