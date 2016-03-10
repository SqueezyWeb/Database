<?php
/**
 * MySqlQuery class file.
 *
 * @package Freyja\Database\Query
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Query;

use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\RuntimeException;
use Freyja\Exceptions\ExceptionInterface;

/**
 * MySqlQuery class.
 *
 * @package Freyja\Database\Query
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.3.0
 */
class MySqlQuery extends Query implements QueryInterface {
  /**
   * Query type.
   *
   * Accepted values: 'select', 'update', 'insert', 'delete'.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $type;

  /**
   * Target table.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $table;

  /**
   * SELECT fields.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $select = array();

  /**
   * Whether SELECT is DISTINCT or not.
   *
   * @since 1.2.0
   * @access private
   * @var boolean
   */
  private $distinct = false;

  /**
   * ORDERBY.
   *
   * Associative array `key => value`, where `key` is the field in which to sort
   * the results and `value` is the direction.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $order_by = array();

  /**
   * GROUPBY.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $group_by;

  /**
   * HAVING clause.
   *
   * Array of strings: field name, operator, value.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $having = array();

  /**
   * UPDATE fields and values.
   *
   * Associative array `key => value`, where `key` is the fields to update, and
   * `value` is the new value.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $update = array();

  /**
   * INSERT fields.
   *
   * @since 1.1.0 Change name and behaviour.
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $insert_fields = array();

  /**
   * INSERT values.
   *
   * Array containing one array for each set of data. Every internal array must
   * have the same length of $insert_fields.
   *
   * @since 1.1.0
   * @access private
   * @var array
   */
  private $insert_values = array();

  /**
   * DELETE modifier.
   *
   * DELETE modifier can be one of the following: MySqlQuery::LOW_PRIORITY,
   * MySqlQuery::QUICK and MySqlQuery::IGNORE.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $delete_modifier;

  /**
   * Table joins.
   *
   * Array of arrays, each of which contains the table to join with, the field
   * name of the first table, the operator, the field name of the second table,
   * and the type of the JOIN.
   * Array structure:
   *  array(
   *    array($table, $first_table_field, $operator, $second_table_field, $type)
   *  );
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $joins = array();

  /**
   * Where clauses.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $where = '';

  /**
   * LIMIT.
   *
   * @since 1.0.0
   * @access private
   * @var int
   */
  private $limit;

  /**
   * OFFSET.
   *
   * @since 1.0.0
   * @access private
   * @var int
   */
  private $offset;

  /**
   * Query result.
   *
   * @since 1.0.0
   * @access private
   * @var mixed
   */
  private $result;

  /**
   * Value delimiter.
   *
   * Used to mark string values, so that they can be recognized and escaped
   * when the query will be executed.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $delimiter = '{esc}';

  /**
   * DELETE modifiers constants.
   *
   * @since 1.0.0
   * @access public
   * @var string
   */
  const LOW_PRIORITY = 'LOW_PRIORITY';
  const QUICK = 'QUICK';
  const IGNORE = 'IGNORE';

  /**
   * Set target table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Table name.
   * @return self
   */
  public function table($name) {
    // Verify that $name is a string.
    if (!is_string($name))
      throw InvalidArgumentException::typeMismatch('table name', $name, 'String or array');

    $this->table = $name;
    return $this;
  }

  /**
   * Set SELECT fields.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string $field Field name.
   * @return self
   */
  public function select($fields) {
    $fields = is_array($fields) ? $fields : func_get_args();
    if (!empty($fields)) {
      $fields = array_filter($fields, 'is_string');
      if (empty($fields))
        throw new InvalidArgumentException(
          sprintf('Fields passed to %s() are not valid.', __NAMESPACE__.__METHOD__)
        );
    }
    $this->select = $fields;
    $this->type = 'select';

    return $this;
  }

  /**
   * Select different (DISTINCT) values.
   *
   * @since 1.2.0
   * @access public
   *
   * @return self
   */
  public function distinct() {
    $this->distinct = true;
    return $this;
  }

  /**
   * Set a select max.
   *
   * @since 1.3.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't a string.
   */
  public function max($field) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');

    return $this->select(sprintf('MAX(%s)', $field));
  }

  /**
   * Set a select min.
   *
   * @since 1.3.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't a string.
   */
  public function min($field) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');

    return $this->select(sprintf('MIN(%s)', $field));
  }

  /**
   * Set a select sum.
   *
   * @since 1.3.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't a string.
   */
  public function sum($field) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');

    return $this->select(sprintf('SUM(%s)', $field));
  }

  /**
   * Set a select avg.
   *
   * @since 1.3.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't a string.
   */
  public function avg($field) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');

    return $this->select(sprintf('AVG(%s)', $field));
  }

  /**
   * Set a select count.
   *
   * @since 1.3.0 Change behaviour.
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't a string.
   */
  public function count($field) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');

    return $this->select(sprintf('COUNT(%s)', $field));
  }

  /**
   * Set a select greatest.
   *
   * @since 1.3.0
   * @access public
   *
   * @param array $field Field names.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't an
   * array, or if its elements aren't strings, or if ithave less than 2 elements.
   */
  public function greatest(array $fields) {
    if (count($fields) < 2)
      throw new InvalidArgumentException(sprintf(
        'Array passed to %s must have at least 2 elements.',
        __METHOD__
      ));
    foreach ($fields as $field)
      if (!is_string($field))
        throw new InvalidArgumentException(sprintf(
          'Every element of the array passed to %s must be a string.',
          __METHOD__
        ));

    return $this->select(sprintf('GREATEST(%s)', join(', ', $fields)));
  }

  /**
   * Set a select round.
   *
   * @since 1.3.0
   * @access public
   *
   * @param string $field Field name.
   * @param int $decimals Optional. Number of decimals to be returned. Default 0.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if first argument isn't
   * a string or if second parameter isn't an integer.
   */
  public function round($field, $decimals = 0) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');
    if (!is_int($decimals))
      throw InvalidArgumentException::typeMismatch('decimals', $decimals, 'Integer');

    return $this->select(sprintf('ROUND(%1$s, %2$s)', $field, $decimals));
  }

  /**
   * Set UPDATE fields and values.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $values Associative array `key => value`, where `key` is the
   * name of the field and `value` is the new value.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if values in $values aren't
   * scalars.
   */
  public function update(array $values) {
    foreach ($values as &$value) {
      try {
        $value = $this->correctValue($value, 'update');
      } catch (ExceptionInterface $e) {
        throw $e;
      }
    }

    $this->update = $values;
    $this->type = 'update';

    return $this;
  }

  /**
   * Set INSERT fields and values.
   *
   * Set the values that will be inserted in the specified fields.
   * Every internal array of $values MUST have the same length of $fields.
   *
   * @since 1.1.0 Add argument and various checks to allow inserting more than a
   * single row to the table.
   * @since 1.0.0
   * @access public
   *
   * @param array $fields Array of strings, every of which is a field name.
   * @param array $values Array containing arrays, every of which represents a
   * row of values to insert.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if elements of $fields
   * aren't strings, or if elements of $values aren't arrays, or if the length
   * of $fields isn't equal to the length of each internal array of $values.
   * @throws Freyja\Exceptions\RuntimeException if elements of the internal
   * arrays of $values aren't valid.
   */
  public function insert(array $fields, array $values) {
    foreach ($fields as $field)
      if (!is_string($field))
        throw InvalidArgumentException::typeMismatch('fields (one of its elements)', $field, 'String');
    $length = count($fields);
    foreach ($values as &$row) {
      if (!is_array($row))
        throw InvalidArgumentException::typeMismatch('values (one of its elements)', $row, 'Array');
      if (count($row) != $length)
        throw new InvalidArgumentException(
          'Every internal array of arguments second argument must be equal to first argument length'
        );
      foreach ($row as &$value) {
        try {
          $value = $this->correctValue($value, 'insert');
        } catch (ExceptionInterface $e) {
          throw $e;
        }
      }
    }

    $this->insert_fields = $fields;
    $this->insert_values = $values;
    $this->type = 'insert';

    return $this;
  }

  /**
   * Set a DELETE query.
   *
   * If clause is set through the method `MySqlQuery::where()`, all rows of the
   * table will be affected.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $modifier Optional. DELETE Modifier. Allowed keywords:
   * `MySqlQuery::LOW_PRIORITY`, `MySqlQuery::QUICK`, `MySqlQuery::IGNORE`.
   * Default null.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $modifier isn't a
   * string and one of the allowed value.
   */
  public function delete($modifier = null) {
    if (!is_string($modifier) && !is_null($modifier))
      throw InvalidArgumentException::typeMismatch('modifier', $modifier, 'String or null');
    $accepted_keywords = array(
      null,
      MySqlQuery::LOW_PRIORITY,
      MySqlQuery::QUICK,
      MySqlQuery::IGNORE
    );
    if (!in_array($modifier, $accepted_keywords))
      throw new InvalidArgumentException(sprintf('Modifier passed to %s() is invalid', __NAMESPACE__.__METHOD__));

    $this->delete_modifier = $modifier;
    $this->type = 'delete';

    return $this;
  }

  /**
   * JOIN tables.
   *
   * JOIN two tables. Second table of the JOIN will be the first argument of
   * this method. The first one will be the one set with the
   * `MySqlQuery::table()` method.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @param string $type Optional. Join type (INNER, LEFT, RIGHT, FULL OUTER).
   * Default: 'inner'.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string or if $operator isn't a valid one, or if $type
   * isn't a valid join type.
   */
  public function join($table, $one, $operator, $two, $type = 'INNER') {
    // Checks on arguments.
    foreach (array('table', 'one', 'operator', 'two', 'type') as $arg)
      if (!is_string($$arg))
        throw InvalidArgumentException::typeMismatch($arg, $$arg, 'String');
    if (!self::isOperatorValid($operator, 'join'))
      throw new InvalidArgumentException(sprintf(
        'Operator %1$s passed to %2$s() is invalid',
        $operator,
        __NAMESPACE__.__METHOD__
      ));
    $type = strtoupper($type);
    if (!in_array($type, array('INNER', 'LEFT', 'RIGHT', 'FULL OUTER')))
      throw new InvalidArgumentException(sprintf(
        'Join type passed to %s() must be a valid join type',
        __NAMESPACE__.__METHOD__
      ));

    $this->joins[] = array($table, $one, $operator, $two, $type);
    return $this;
  }

  /**
   * Perform a LEFT JOIN.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws Freyja\Exceptions\RuntimeException if $operator isn't a valid
   * operator.
   */
  public function leftJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'LEFT');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Perform a RIGHT JOIN.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws Freyja\Exceptions\RuntimeException if $operator isn't a valid
   * operator.
   */
  public function rightJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'RIGHT');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Perform a FULL OUTER JOIN.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws Freyja\Exceptions\RuntimeException if $operator isn't a valid
   * operator.
   */
  public function fullOuterJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'FULL OUTER');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Set order to results.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param string $direction Optional. Set the direction of the sort. Allowed
   * values: 'ASC', 'DESC'. Default: 'ASC'.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $field and
   * $direction aren't strings.
   * @throws Freyja\Exceptions\RuntimeException if $direction isn't 'ASC' or
   * 'DESC';
   */
  public function orderBy($field, $direction = 'ASC') {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field name', $field, 'String');
    if (!is_string($direction))
      throw InvalidArgumentException::typeMismatch('direction', $direction, 'String');
    $direction = strtoupper($direction);
    if ($direction != 'ASC' && $direction != 'DESC')
      throw new RuntimeException('$direction passed to `MySqlQuery::orderBy()` must be \'ASC\' or \'DESC\'');

    $this->order_by[$field] = $direction;
    return $this;
  }

  /**
   * Perform a GROUPBY.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function groupBy($field) {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field name', $field, 'String');

    $this->group_by = $field;
    return $this;
  }

  /**
   * Set HAVING clause.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param string $operator Operator.
   * @param mixed $value Value.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $field and
   * $operator aren't strings, or if $operator isn't one of the allowed ones, or
   * if $value isn't in the correct form.
   */
  public function having($field, $operator, $value) {
    foreach(array('field', 'operator') as $arg)
      if (!is_string($$arg))
        throw InvalidArgumentException::typeMismatch($arg, $$arg, 'String');

    if (!self::isOperatorValid($operator, __METHOD__))
      throw new InvalidArgumentException(sprintf(
        'Operator %1$s passed to %2$s() is invalid',
        $operator,
        __NAMESPACE__.__METHOD__
      ));

    $operator = strtoupper($operator);
    if (is_array($value) && $operator == 'BETWEEN') {
      if (count($value) != 2)
        throw new InvalidArgumentException(sprintf('Value passed to %s() is invalid', __NAMESPACE__.__METHOD__));
      try {
        $value = array_map(array($this, 'correctValue'), $value, array_fill(0, count($value), __METHOD__));
      } catch (ExceptionInterface $e) {
        throw $e;
      }
      $value = join(' AND ', $value);
    } else {
      try {
        $value = $this->correctValue($value, 'having');
      } catch (ExceptionInterface $e) {
        throw $e;
      }
    }

    $this->having = array($field, $operator, $value);
    return $this;
  }

  /**
   * Set a LIMIT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $limit Number of maximum rows returned (LIMIT).
   * @param int $offset Optional. Specify the OFFSET. Default: null.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the arguments
   * aren't numeric.
   */
  public function limit($limit, $offset = null) {
    if (!is_numeric($limit))
      throw InvalidArgumentException::typeMismatch('limit', $limit, 'Numeric');
    if (!is_numeric($offset) && !is_null($offset))
      throw InvalidArgumentException::typeMismatch('offset', $offset, 'Numeric or null');

    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }

  /**
   * Set the query to return only the first row.
   *
   * It is equivalent to call `MySqlQuery::limit(1, 0)`. In other words, this
   * method will perform a `LIMIT 0, 1` in the query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function first() {
    try {
      return $this->limit(1, 0);
    } catch (ExceptionInterface $e) {
      throw $e;
    }
    return $this;
  }

  /**
   * Set WHERE clauses.
   *
   * Set where clauses, linking them with the `AND` operator.
   * Arguments can be passed in two forms. The simple one is useful for setting
   * one condition, and consists of passing an argument for the field, one for
   * the operator (can be omitted if '=') and one for the value.
   * The other one consists of passing an array containing an array for every
   * clause, each of which must contain two or three elements, in the same form
   * as the first one.
   * Every other way will raise an exception.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string Where clauses.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if the arguments aren't in the
   * correct form.
   */
  public function where($clauses) {
    $args = func_get_args();
    try {
      if (count($args) == 1 && is_array($clauses))
        $this->processWhere($clauses, 'where');
      else
        $this->processWhere(array($args), 'where');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
    return $this;
  }

  /**
   * Set WHERE clauses.
   *
   * Set where clauses, linking them with the `OR` operator.
   * The method behave like `MySqlQuery::where()`.
   * @see MySqlQuery::where()
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string Where clauses.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if the arguments aren't in the
   * correct form.
   */
  public function orWhere($clauses) {
    $args = func_get_args();
    try {
      if (count($args) == 1)
        $this->processWhere($clauses, 'orWhere');
      else
        $this->processWhere(array($args), 'orWhere');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
    return $this;
  }

  /**
   * Set WHERE clauses.
   *
   * Set clauses like: 'WHERE `field` IN (`value` AND `value` AND `value` ...)'.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param array $values Values to check with.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if $value elements aren't in the
   * correct form.
   * @throws Freyja\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function whereIn($field, array $values) {
    try {
      $this->processWhereIn($field, $values, 'whereIn');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
    return $this;
  }

  /**
   * Set WHERE clauses.
   *
   * Set clauses like: 'WHERE `field` NOT IN (`value` AND `value` ...)'.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param array $values Values to check with.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if $value elements aren't in the
   * correct form.
   * @throws Freyja\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function whereNotIn($field, array $values) {
    try {
      $this->processWhereIn($field, $values, 'whereNotIn');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
    return $this;
  }

  /**
   * Set raw where clauses.
   *
   * Set the WHERE clause of the query to the specified string.
   * Any string value MUST be put between delimiters, so
   * `MySqlQuery::getDelimiter()` MUST be called before and after every string
   * value.
   *
   * Note that calling this method will replace every other where clauses
   * previously set. Otherwise, calling other where methods after this one will
   * append other clauses to the one set by this method.
   * @see MySqlQuery::getDelimiter
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $where
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if argument isn't a
   * string.
   */
  public function whereRaw($where) {
    if (!is_string($where))
      throw InvalidArgumentException::typeMismatch('where', $where, 'String');

    $this->where = $where;
    return $this;
  }

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if $table property isn't set or
   * if there is some inconsistency with the data required by every specific
   * method.
   */
  public function build() {
    try {
      $query = 'build'.ucfirst(strtolower($this->type));
      return $this->{$query}();
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Retrieve delimiter.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   */
  public function getDelimiter() {
    return $this->delimiter;
  }

  /**
   * Process WHERE clauses.
   *
   * @since 1.0.0
   * @access private
   *
   * @param array $args Arguments of the public method.
   * @param string $method Name of the method that called this one.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if the arguments aren't
   * in the correct form.
   */
  private function processWhere(array $args, $method = 'where') {
    foreach ($args as $arg) {
      // Verify that every element is an array with 2 or 3 elements inside.
      if (!is_array($arg) || count($arg) < 2 || count($arg) > 3)
        throw new InvalidArgumentException(sprintf(
          'Invalid data passed to `MySqlQuery::%s()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed',
          $method
        ));
      // Chech every element of the internal arrays to be strings (or array if
      // it is the 3rd element)
      $count = 0;
      foreach ($arg as $element) {
        if (!is_scalar($element) && !is_null($element) && !(is_array($element) && $count == 2))
          throw new InvalidArgumentException(sprintf(
            'Some elements of some clauses passed to `MySqlQuery::%s()` are invalid',
            $method
          ));
        $count++;
      }
    }
    // $args is in the correct form.
    try {
      $this->buildWhereClause($args, ($method == 'where') ? 'AND' : 'OR');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Process WHERE clauses.
   *
   * Process `WHERE ... IN ...` and `WHERE ... NOT IN ...` clauses.
   *
   * @since 1.0.0
   * @access private
   *
   * @param string $field Field name.
   * @param array $values Values to check with.
   * @param string $method Name of the method that called this one.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if $value elements aren't in the
   * correct form.
   * @throws Freyja\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  private function processWhereIn($field, array $values, $method = 'whereIn') {
    if (!is_string($field))
      throw InvalidArgumentException::typeMismatch('field', $field, 'String');

    $where = $this->where;

    $correct = array_map(array($this, 'correctValue'), $values, array_fill(0, count($values), $method));
    $where .= sprintf(
      '%1$s %2$s %3$sIN(%4$s)',
      empty($where) ? 'WHERE' : ' AND',
      $field,
      ($method == 'whereNotIn') ? 'NOT ' : '',
      join(', ', $correct)
    );


    $this->where = $where;
  }

  /**
   * Build WHERE clause.
   *
   * Transform a list of arguments in a string of WHERE conditions.
   *
   * @since 1.0.0
   * @access private
   *
   * @param array $clauses Array containing arrays, each of which is a `WHERE`
   * clause.
   * @param string $operator `AND` or `OR`.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if the arguments aren't
   * in the correct form.
   */
  private function buildWhereClause(array $clauses, $operator) {
    $where = $this->where;
    $method = ($operator == 'AND') ? 'where' : 'orWhere';

    foreach ($clauses as $clause) {
      if (count($clause) == 2) {
        $value = $clause[1];
        $oprt = '=';
      } elseif (count($clause) == 3) {
        $value = $clause[2];
        $oprt = $clause[1];
        if (!self::isOperatorValid($oprt, 'buildWhereClause'))
          throw new InvalidArgumentException(
            'One of the operators passed to `MySqlQuery::'.$method.'()` is invalid'
          );
      }

      if (!empty($where))
        $where .= ' '.$operator.' ';
      else
        $where = 'WHERE ';

      $oprt = strtoupper($oprt);
      if (is_array($value) && $oprt == 'BETWEEN') {
        // Replace the value with a string that contains the values in the
        // array, linked by 'AND'.
        // If the array doesn't contains exactly 2 values, raise an exception.
        if (count($value) != 2)
          throw new InvalidArgumentException(
            'Operator BETWEEN requires a range specification as an array with two elements'
          );
        try {
          $value = array_map(array($this, 'correctValue'), $value, array_fill(0, count($value), __METHOD__));
        } catch (ExceptionInterface $e) {
          throw $e;
        }
        $value = join(' AND ', $value);
      } elseif (!is_array($value) && $oprt == 'BETWEEN') {
        throw new InvalidArgumentException('Operator BETWEEN requires a range specification as an array with two elements, no array was given');
      } elseif (is_array($value) && $oprt != 'BETWEEN') {
        throw new InvalidArgumentException(sprintf(
          'Any operator except BETWEEN accept only a single value in method `MySqlQuery::%s()`',
          $method
        ));
      } else {
        try {
          $value = $this->correctValue($value, $method);
        } catch (ExceptionInterface $e) {
          throw $e;
        }
      }
      // Any other case (e.g. value is integer) is ok as it is.

      $where .= $clause[0].' '.$oprt.' '.$value;
    }

    $this->where = $where;
  }

  /**
   * Correct value.
   *
   * @since 1.0.0
   * @access private
   *
   * @param mixed $value Scalar value.
   * @param string $method Name of the method that called this one (directly or
   * indirectly).
   * @return mixed The correct value.
   *
   * @throws Freyja\Exceptions\RuntimeException if the value is an array.
   */
  private function correctValue($value, $method) {
    if (is_string($value)) {
      // Put quotes and delimiters around the value if it is a string.
      $value = sprintf(
        '\'%1$s%2$s%1$s\'',
        $this->delimiter,
        $value
      );
    } elseif (is_null($value)) {
      // Replace the value with a string 'NULL' if it is null.
      $value = 'NULL';
    } elseif (is_bool($value)) {
      // Replace the value with a string 'TRUE' or 'FALSE' if it is boolean.
      $value = ($value == true) ? 'TRUE' : 'FALSE';
    } elseif (!is_scalar($value)) {
      $serialized = serialize($value);
      $unserialized = unserialize($serialized);
      if ($unserialized != $value)
        throw new RuntimeException(
          'Arguments passed to `MySqlQuery::'.$method.'()` aren\'t in the correct form'
        );
      else
        $value = $this->delimiter.$serialized.$this->delimiter;
    }
    // Any other case (e.g. value is integer) is ok as it is.
    return $value;
  }

  /**
   * Verify the operator.
   *
   * Checks that the operator passed is a valid one.
   *
   * @since 1.0.0
   * @access private
   * @static
   *
   * @param string $operator Operator that needs to be verified.
   * @param string $method Name of the method that called this one.
   * @return boolean Whether the operator is valid or not.
   */
  private static function isOperatorValid($operator, $method) {
    if ($method == 'join')
      $valid_operators = array('=', '>', '>=', '<', '<=', '!=', 'LIKE');
    else
      $valid_operators = array('=', '>', '>=', '<', '<=', '!=', 'BETWEEN', 'LIKE');

    return is_string($operator) && in_array(strtoupper($operator), $valid_operators);
  }

  /**
   * Build SELECT query string.
   *
   * @since 1.2.0 Add DISTINCT selection.
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if $table property isn't set.
   */
  private function buildSelect() {
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');

    // Create the `SELECT` part.
    $query = sprintf(
      'SELECT %s',
      $this->distinct ? 'DISTINCT ' : ''
    );
    if (!is_array($this->select) || empty($this->select)) {
      $query .= '*';
    } else {
      $query .= join(', ', $this->select);
    }

    // Append the `FROM` part.
    $query .= ' FROM '.$this->table;

    // Append the `JOIN` part.
    if (!empty($this->joins)) {
      foreach ($this->joins as $join) {
        // $join[0] --> second table.
        // $join[1] --> field one.
        // $join[2] --> operator.
        // $join[3] --> field two.
        // $join[4] --> join type.
        $query .= sprintf(
          ' %1$s JOIN %2$s ON %3$s %4$s %5$s',
          $join[4],
          $join[0],
          $join[1],
          $join[2],
          $join[3]
        );
      }
    }

    // Append the `WHERE` part.
    $query .= sprintf(
      '%1$s%2$s',
      empty($this->where) ? '' : ' ',
      $this->where
    );

    // Append the `GROUP BY` part.
    if (isset($this->group_by)) {
      $query .= ' GROUP BY '.$this->group_by;

      // Append the `HAVING` part.
      if (!empty($this->having)) {
        $query .= sprintf(
          ' HAVING %1$s %2$s %3$s',
          $this->having[0],               // field
          $this->having[1],               // operator
          $this->having[2]                // value
        );
      }
    }

    // Append the `ORDER BY` part.
    $query .= $this->buildOrderBy();

    // Append the `LIMIT` part.
    $query .= $this->buildLimit();

    return $query;
  }

  /**
   * Build COUNT query part.
   *
   * @deprecated 1.3.0 No longer used by internal code.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @codeCoverageIgnore
   */
  private function buildCount() {
    $part = '';

    if (isset($this->count) && $this->count === true)
      $part .= 'COUNT(*)';
    elseif (is_array($this->count))
      $part = 'COUNT('.join(', ', $this->count).')';

    return $part;
  }

  /**
   * Build UPDATE query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if $table and $update properties
   * aren't set.
   */
  private function buildUpdate() {
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');
    if (!isset($this->update) || empty($this->update))
      throw new RuntimeException('Cannot execute an UPDATE query without updating anything');

    // `UPDATE` part.
    $query = 'UPDATE '.$this->table.' ';

    // Append `SET` part.
    $query .= 'SET ';
    $query .= join(', ', array_map(function($field, $value) {
      return $field.' = '.$value;
    }, array_keys($this->update), array_values($this->update)));

    // Append `WHERE` part.
    $query .= sprintf(
      '%1$s%2$s',
      empty($this->where) ? '' : ' ',
      $this->where
    );

    // Append `ORDER BY` part.
    $query .= $this->buildOrderBy();

    // Append `LIMIT` part.
    $query .= $this->buildLimit();

    return $query;
  }

  /**
   * Build INSERT query.
   *
   * @since 1.1.0 Add check and modify outputed query to allow the insertion of
   * more than a single row to the table.
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if $table and $insert properties
   * aren't set.
   */
  private function buildInsert() {
    // `INSERT INTO` part.
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');
    if (!isset($this->insert_values) || empty($this->insert_values))
      throw new RuntimeException('Cannot execute an INSERT query without inserting anything');
    if (!isset($this->insert_fields) || empty($this->insert_fields))
      throw new RuntimeException('You have to specify some fields for an INSERT query');

    $query = sprintf(
      'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
      $this->table,
      join(', ', $this->insert_fields),
      join('), (', array_map(function($row) {
        return join(', ', $row);
      }, $this->insert_values))
    );

    return $query;
  }

  /**
   * Build DELETE query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if $table property isn't set.
   */
  private function buildDelete() {
    // `DELETE` part.
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');

    $query = sprintf(
      'DELETE %1$sFROM %2$s',
      isset($this->delete_modifier) ? $this->delete_modifier.' ' : '',
      $this->table
    );

    // Append `WHERE` part.
    $query .= sprintf(
      '%1$s%2$s',
      empty($this->where) ? '' : ' ',
      $this->where
    );

    // Append `ORDER BY` part.
    $query .= $this->buildOrderBy();

    // Append `LIMIT` part.
    $query .= $this->buildLimit();

    return $query;
  }

  /**
   * Build ORDER BY query part.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   */
  private function buildOrderBy() {
    if (empty($this->order_by))
      return '';

    return ' ORDER BY '.join(', ', array_map(function($field, $direction) {
      return $field.' '.$direction;
    }, array_keys($this->order_by), array_values($this->order_by)));
  }

  /**
   * Build LIMIT query part.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   */
  private function buildLimit() {
    if (is_null($this->limit))
      return '';

    return sprintf(
      ' LIMIT %1$s%2$s',
      isset($this->offset) ? $this->offset.', ' : '',
      $this->limit
    );
  }
}
