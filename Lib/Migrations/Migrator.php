<?php
/**
 * Freyja Database Migrator.
 *
 * @package Freyja\Database\Migrations
 * @copyright 2016 SqueezyWeb
 * @since 0.3.0
 */

namespace Freyja\Database\Migrations;

use Illuminate\Filesystem\Filesystem;
// TODO: use Freyja\Support\Array;
// TODO: use Freyja\Support\String;

/**
 * Freyja Database Migrator.
 *
 * @package Freyja\Database\Migrations
 * @author Mattia Migliorini <mattia@squeezyweb.com>
 * @since 0.3.0
 * @version 1.0.0
 */
class Migrator {
  /**
   * Migration repository implementation.
   *
   * @since 1.0.0
   * @access protected
   * @var RepositoryInterface
   */
  protected $repository;

  /**
   * Filesystem instance.
   *
   * @since 1.0.0
   * @access protected
   * @var Filesystem
   */
  protected $files;

  /**
   * Notes for the current operation.
   *
   * @since 1.0.0
   * @access protected
   * @var array
   */
  protected $notes = array();

  /**
   * Create new migrator instance.
   *
   * @since 1.0.0
   * @access public
   *
   * @param RepositoryInterface $repository Migration repository instance.
   * @param Filesystem $files Filesystem instance.
   */
  public function __construct(RepositoryInterface $repository, Filesystem $files) {
    $this->repository = $repository;
    $this->files = $files;
  }

  /**
   * Run outstanding migrations at given path.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $path Path to the migrations files.
   * @param array $options Optional. Migration options. Default empty.
   */
  public function run($path, array $options = array()) {
    // Reset notes array.
    $this->notes = array();

    $files = $this->getMigrationFiles($path);

    // Once we grab alla of the migration files for the path, we will compare
    // them against the migrations that have already been run for this package
    // then run each of the outstanding migrations against the repository.
    $ran = $this->repository->getRan();

    $migrations = array_diff_key($files, array_flip($ran));

    $this->requireFiles($path, $migrations);

    $this->runMigrationList($migrations, $options);
  }

  /**
   * Run array of migrations.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $migrations Array of migrations.
   * @param array $options Optional. Migration options. Default empty.
   */
  public function runMigrationList($migrations, array $options = array()) {
    // First we will just make sure that there are any migrations to run.
    // If there aren't, we will just make a note of it to the developer so they
    // are aware that all of the migrations have been run against this database
    // system.
    if (count($migrations) == 0) {
      $this->note('<info>Nothing to migrate</info>');
      return;
    }

    $simulate = Array::get($options, 'simulate', false);

    // Once we have the array of migrations, we will spin through them and run
    // the migrations "up" so the changes are made to the databases. We'll then
    // log that the migration was run so we don't repeat it next time we execute.
    foreach ($migrations as $batch => $file)
      $this->runUp($file, $batch, $simulate);
  }

  /**
   * Run "up" a migration instance.
   *
   * @since 1.0.0
   * @access protected
   *
   * @param string $file Name of migration file.
   * @param int $batch Migration batch number.
   * @param bool $simulate Whether to simulate the action instead of run it.
   */
  protected function runUp($file, $batch, $simulate) {
    // First we will resolve a "real" instance of the migration class from this
    // migration file name. Once we have the instances we can run the actual
    // command such as "up" or "down", or we can just simulate the action.
    $migration = $this->resolve($file);

    if ($simulate)
      return $this->simulate($migration, 'up');

    $migration->up();

    // Once we have run a migrations class, we will log that it was run in this
    // repository so that we don't try to run it next time we do a migration in
    // the application. A migration repository keeps the migrate order.
    $this->repository->log($file, $batch);

    $this->note(sprintf('<info>Migrated:</info> %s', $file));
  }

  /**
   * Rollback last migration operation.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $path Path to the migration files.
   * @param bool $simulate Optional. Default false.
   */
  public function rollback($path, $simulate = false) {
    // Reset notes array.
    $this->notes = array();

    // We want to pull in the last migration that ran on the previous migration
    // operation. We'll then reverse that migration and run its method "down"
    // to reverse the last migration operation which ran.
    $lastMigration = $this->repository->getLast();

    if (empty($lastMigration)) {
      $this->note('<info>Nothing to rollback.</info>');
      return;
    }

    $migration = $lastMigration['migration'];
    $this->requireFiles($path, array($migration));

    $this->runDown($migration, $simulate);
  }

  /**
   * Rolls all of the currently applied migrations back.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $path Path to the migration files.
   * @param bool $simulate Optional. Default false.
   *
   * @return int Number of migrations resetted.
   */
  public function reset($path, $pretend = false) {
    // Reset notes array.
    $this->notes = array();

    $ran = $this->repository->getRan();

    $count = count($ran);

    if ($count == 0) {
      $this->note('<info>Nothing to rollback.</info>');
    } else {
      $files = $this->getMigrationFiles($path);
      $files = array_reverse(array_diff_key($files, array_flip($ran)));
      $this->requireFiles($path, $files);
      foreach ($files as $migration) {
        $this->runDown($migration, $pretend);
      }
    }

    return $count;
  }

  /**
   * Run "down" migration instance.
   *
   * @since 1.0.0
   * @access protected
   *
   * @param string $file Name of migration file.
   * @param bool $simulate
   */
  protected function runDown($file, $simulate) {
    $migration = $this->resolve($file);

    if ($simulate)
      return $this->simulate($migration, 'down');

    $migration->down();

    // Once we have successfully run the migration "down" we will remove it from
    // the migration repository so it will be considered to have not been run by
    // the application then will be able to fire by any later operation.
    $this->repository->delete($migration);

    $this->note(sprintf('<info>Rolled back:</info> %s', $file));
  }

  /**
   * Get all migration files in a given path.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $path Migrations path.
   *
   * @return array Associative array batch => file.
   */
  public function getMigrationFiles($path) {
    $files = $this->files->glob($path.'/*_*.php');

    // Once we have the array of files in the directory we will just remove the
    // extension and take the batch number and basename of the file which is all
    // we need when finding the migrations that haven't been run against the
    // repository.
    if ($files === false)
      return array();

    $files = array_map(function($file) {
      return str_replace('.php', '', basename($file));
    }, $files);

    // Once we have all of the formatted file names we will sort them and since
    // they all start with a timestamp this should give us the migrations in the
    // order they were actually created by the application developers.
    $files = sort($files);

    // Create an array containing the migration batches.
    $batches = array_map(function($file) {
      list($batch) = explode('_', $file);
      return $batch;
    }, $files);

    // Combine the two array to have an associative array with batches as keys
    // and files as values.
    return array_combine($batches, $files);
  }

  /**
   * Require all migration files in a given path.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $path Path to the migrations files.
   * @param array $files Array of files to require.
   */
  public function requireFiles($path, array $files) {
    foreach ($files as $file)
      $this->files->requireOnce(sprintf('%1$s/%2$s.php', $path, $file));
  }

  /**
   * Simulate run on migrations.
   *
   * @since 1.0.0
   * @access protected
   *
   * @param MigrationInterface $migration
   * @param string $method
   */
  protected function simulate(MigrationInterface $migration, $method) {
    foreach ($this->getQueries($migration, $method) as $query) {
      $name = get_class($migration);
      $this->note(sprintf('<info>%1$s:</info> %2$s', $name, $query['query']));
    }
  }

  /**
   * Get all queries that would be run for a migration.
   *
   * @since 1.0.0
   * @access protected
   *
   * @param MigrationInterface $migration
   * @param string $method
   *
   * @return array
   */
  protected function getQueries(MigrationInterface $migration, $method) {
    // TODO: for every migration, it should return the queries it would execute.
  }

  /**
   * Resolve migration instance from file.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $file
   *
   * @return object
   */
  public function resolve($file) {
    $class = implode('_', array_slice(explode('_', $file), 1));

    $class = String::studly($file);

    return new $class;
  }

  /**
   * Raise a note event for the migrator.
   *
   * @since 1.0.0
   * @access protected
   *
   * @param string $message
   */
  protected function note($message) {
    $this->notes[] = $message;
  }

  /**
   * Retrieve notes for the last operation.
   *
   * @since 1.0.0
   * @access public
   *
   * @return array
   */
  public function getNotes() {
    return $this->notes;
  }

  /**
   * Retrieve migration repository instance.
   *
   * @since 1.0.0
   * @access public
   *
   * @return RepositoryInterface
   */
  public function getRepository() {
    return $this->repository;
  }

  /**
   * Determine if migration repository exists.
   *
   * @since 1.0.0
   * @access public
   *
   * @return bool
   */
  public function repositoryExists() {
    return $this->repository->repositoryExists();
  }

  /**
   * Retrieve filesystem instance.
   *
   * @since 1.0.0
   * @access public
   *
   * @return Filesystem
   */
  public function getFilesystem() {
    return $this->files;
  }
}
