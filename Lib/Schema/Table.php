<?php
/**
 * Table class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Database\Query;
use Freyja\Database\QueryInterface;
use Freyja\Database\Schema\Field;
use Freyja\Exceptions\InvalidArgumentException as InvArgExcp;

/**
 * Table class.
 *
 * @package Freyja\Database\Schema
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class Table extends Query implements QueryInterface {
  /**
   * Table name.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $name;

  /**
   * Table fields.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $fields = array();

  /**
   * Table character set.
   *
   * @since 1.0.0
   * @access private
   * @var string;
   */
  private $charset;

  /**
   * Table collation.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $collation;

  /**
   * Table engine.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $engine;

  /**
   * Class constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Table name.
   * @param array $fields Fields of the table.
   * @param string $charset Optional. Table character set. Default 'utf8'.
   * @param string $collation Optional. Table collation.
   * Default 'utf8_unicode_ci'.
   * @param string $engine Optional. Table engine. Default 'InnoDB'.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $name or $charset or
   * $collation or $engine aren't strings, or if $fields elements aren't
   * Freyja\Database\Schema\Field objects.
   */
  public function __construct($name, array $fields, $charset = 'utf8', $collation = 'utf8_unicode_ci', $engine = 'InnoDB') {
    foreach (array('name', 'charset', 'collation', 'engine') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');
    foreach ($fields as $field)
      if (!is_a($field, 'Freyja\Database\Schema\Field'))
        throw InvArgExcp::typeMismatch('field', $field, 'Freyja\Database\Schema\Field');

    $this->name = $name;
    $this->fields = $fields;
    $this->charset = $charset;
    $this->collation = $collation;
    $this->engine = $engine;
  }

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   */
  public function build();
}
