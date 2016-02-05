<?php
/**
 * Schema class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Database\Driver;
use Freyja\Database\Table;
use Freyja\Database\Database;
use Symfony\Component\Yaml\Yaml;

/**
 * Schema class.
 *
 * @package Freyja\Database\Schema
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class Schema {
  /**
   * Database schema.
   *
   * @todo: put an example.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $schema = array();

  /**
   * Database.
   *
   * @since 1.0.0
   * @access private
   * @var Freyja\Database\Database
   */
  private $database;

  /**
   * Class constructor.
   *
   * The Database argument MUST be connected before passing it to the
   * constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Database $database
   */
  public function __construct(Database $database) {
    $this->database = $database;

    $filename = getcwd().'/db/Schema.yml';
    if (file_exists($filename)) {
      $schema = Yaml::parse(file_get_contents($filename));
      $db_name = $schema[$database->getName()];
      if (isset($db_name))
        $this->schema = $db_name;
    }
  }

  /**
   * Create table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Schema\Table $table
   */
  public function create(Table $table) {
    // Check if table already exists in $schema['tables'].
    // Create table.
    // Update schema.
    // Put schema back to his place.
    //
    // Before everything check if schema empty; if so, create the structure of
    // the array with the db name and everything, and the table.
    // Execute query.
  }
}
