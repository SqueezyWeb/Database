<?php
/**
 * Query class file.
 *
 * @package Freyja\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 1.0.0
 */

namespace Freyja\Database;

use SqueezyWeb\Exceptions\InvalidArgumentException as InvArgExcp;
use \RuntimeException;

/**
 * Query class.
 *
 * @package Freyja\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Query {
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
   * COUNT.
   *
   * True correspond to `COUNT(*)`. Or array containing the fields to be
   * counted. Default false.
   *
   * @since 1.0.0
   * @access private
   * @var boolean|array
   */
  private $count = false;

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
   * INSERT fields and values.
   *
   * Associative array `key => value`, where `key` is the fields to insert, and
   * `value` is the new value.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $insert = array();

  /**
   * DELETE modifier.
   *
   * DELETE modifier can be one of the following: 'LOW_PRIORITY', 'QUICK' and
   * 'IGNORE'.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $delete_modifier = '';

  /**
   * Table joins.
   *
   * Array of arrays, each of which contains the table to join with, the field
   * name of the first table, the operator, the field name of the second table,
   * and the type of the JOIN.
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
      throw InvArgExcp::typeMismatch('table name', $name, 'String or array');

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
    $this->select = array_filter($fields, 'is_string');
    $this->type = 'select';

    return $this;
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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if field names in
   * $values aren't strings.
   * @throws \RuntimeException if values in $values aren't scalars.
   */
  public function update(array $values) {
    foreach ($values as $field => &$value) {
      if (!is_string($field))
        throw InvArgExcp::typeMismatch('field name', $field, 'String');
      try {
        $value = self::correctValue($value, 'update');
      } catch (Exception $e) {
        throw $e;
      }
    }

    $this->update = $values;
    $this->type = 'update';

    return $this;
  }

  /**
   * Set COUNT.
   *
   * Set COUNT to all field or to some specific fields (passing an array
   * containing strings).
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $count Optional. List of fields to which apply the COUNT
   * selector. Default: empty array.
   * @return self
   */
  public function count(array $fields = array()) {
    if (empty($fields))
      $this->count = true;
    else
      $this->count = array_filter($fields, 'is_string');
    $this->type = 'select';

    return $this;
  }

  /**
   * Set INSERT fields and values.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $values Associative array `key => value`, where `key` is the
   * field and `value` is the new value.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if field names in
   * $values aren't strings.
   * @throws \RuntimeException if values in $values aren't scalars.
   */
  public function insert(array $values) {
    foreach ($values as $field => &$value) {
      if (!is_string($field))
        throw InvArgExcp::typeMismatch('field name', $field, 'String');
      try {
        $value = self::correctValue($value, 'update');
      } catch (Exception $e) {
        throw $e;
      }
    }

    $this->insert = $values;
    $this->type = 'insert';

    return $this;
  }

  /**
   * Set a DELETE query.
   *
   * If clause is set through the method `Query::where()`, all rows of the table
   * will be affected.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $modifier Optional. DELETE Modifier. Allowed keywords: '',
   * 'LOW_PRIORITY', 'QUICK', 'IGNORE' (either uppercase or lowercase).
   * Default: ''.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $modifier isn't a
   * string.
   * @throws \RuntimeException if $modifier isn't one of the allowed value.
   */
  public function delete($modifier = '') {
    if (!is_string($modifier))
      throw InvArgExcp::typeMismatch('delete modifier', $modifier, 'String');
    if (!in_array($modifier, $accepted_keywords))
      throw new RuntimeException('Modifier passed to `Query::delete()` isn\'t one of the allowed keywords');

    $this->delete_modifier = $modifier;
    $this->type = 'delete';
  }

  /**
   * JOIN tables.
   *
   * JOIN two tables. Second table of the JOIN will be the first argument of
   * this method. The first one will be the one set with the `Query::table()`
   * method.
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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator or if $type
   * isn't a valid join type.
   */
  public function join($table, $one, $operator, $two, $type = 'INNER') {
    // Checks on arguments.
    foreach (array('table', 'one', 'operator', 'two', 'type') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');
    if (!self::isOperatorValid($operator, 'join'))
      throw new RuntimeException('Operator passed to `Query::join()` must be a valid operator');
    $type = strtoupper($type);
    if (!in_array($type, array('INNER', 'LEFT', 'RIGHT', 'FULL OUTER')))
      throw new RuntimeException('Join type passed to `Query::join()` must be a valid join type');

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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator.
   */
  public function leftJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'LEFT');
    } catch (Exception $e) {
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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator.
   */
  public function rightJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'RIGHT');
    } catch (Exception $e) {
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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator.
   */
  public function fullOuterJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'FULL OUTER');
    } catch (Exception $e) {
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
   * @param string $direction Optional. Set the direction of the sort. Permitted
   * values: 'asc', 'desc'. Default: 'asc'.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field and
   * $direction aren't strings.
   * @throws \RuntimeException if $direction isn't 'ASC' or 'DESC';
   */
  public function orderBy($field, $direction = 'ASC') {
    if (!is_string($field))
      throw InvArgExcp::typeMismatch('field name', $field, 'String');
    if (!is_string($direction))
      throw InvArgExcp::typeMismatch('direction', $direction, 'String');
    $direction = strtoupper($direction);
    if ($direction != 'ASC' && $direction != 'DESC')
      throw new RuntimeException('$direction passed to `Query::orderBy()` must be \'ASC\' or \'DESC\'');

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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function groupBy($field) {
    if (!is_string($field))
      throw InvArgExcp::typeMismatch('field name', $field, 'String');

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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field and
   * $operator aren't strings.
   * @throws \RuntimeException if $operator isn't one of the allowed ones, or if
   * $value isn't in the correct form.
   */
  public function having($field, $operator, $value) {
    foreach(array('field', 'operator') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');

    if (!self::isOperatorValid($operator))
      throw new RuntimeException('Operator passed to `Query::having()` must be a valid one');

    $operator = strtoupper($operator);
    if (is_array($value) && $operator == 'BETWEEN') {
      if (count($value) != 2)
        throw new RuntimeException('Value passed to `Query::having()` isn\'t in the correct form');
      try {
        $value[0] = self::correctValue($value[0], 'having');
        $value[1] = self::correctValue($value[1], 'having');
      } catch (Exception $e) {
        throw $e;
      }
      $value = $value[0].' AND '.$value[1];
    } else {
      try {
        $value = self::correctValue($value, 'having');
      } catch (Exception $e) {
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
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments aren't numeric.
   */
  public function limit($limit, $offset = null) {
    if (!is_numeric($limit))
      throw InvArgExcp::typeMismatch('limit', $limit, 'Numeric');
    if (!is_numeric($offset) && !is_null($offset))
      throw InvArgExcp::typeMismatch('offset', $offset, 'Numeric or null');

    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }

  /**
   * Set the query to return only the first row.
   *
   * It is equivalent to call `Query::limit(1, 0)`. In other words, this method
   * will perform a `LIMIT 0, 1` in the query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function first() {
    try {
      return $this->limit(1, 0);
    } catch (Exception $e) {
      throw $e;
    }
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
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  public function where($clauses) {
    try {
      return $this->processWhere(func_get_args(), 'where');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Set WHERE clauses.
   *
   * Set where clauses, linking them with the `OR` operator.
   * The method behave like `Query::where()`.
   * @see Query::where()
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string Where clauses.
   * @return self
   *
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  public function orWhere($clauses) {
    try {
      return $this->processWhere(func_get_args(), 'orWhere');
    } catch (Exception $e) {
      throw $e;
    }
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
   * @throws \RuntimeException if $value elements aren't in the correct form.
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function whereIn($field, array $values) {
    try {
      return $this->processWhereIn($field, $values, 'whereIn');
    } catch (Exception $e) {
      throw $e;
    }
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
   * @throws \RuntimeException if $value elements aren't in the correct form.
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function whereNotIn($field, array $values) {
    try {
      return $this->processWhereIn($field, $values, 'whereNotIn');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Process WHERE clauses.
   *
   * @since 1.0.0
   * @access private
   *
   * @param array $args Arguments of the public method.
   * @param string $method Name of the method that called this one.
   * @return self
   *
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  private function processWhere(array $args, $method = 'where') {
    if (is_array($args[0])) {
      // Method was called with an array of clauses.
      foreach ($args[0] as $clause) {
        // Check if the array has other arrays or not, and if it has , then
        // check if every internal array has only scalar inside.
        if (!is_array($clause) || count($clause) < 2 || count($clause) > 3)
          throw new RuntimeException('Arguments passed to `Query::'.$method.'()` aren\'t in the correct form');
        $count = 0;
        foreach ($clause as $scalar) {
          if (!is_scalar($scalar) && (!is_array($scalar) || $count != 3))
            throw new RuntimeException('Arguments passed to `Query::'.$method.'()` aren\'t in the correct form');
          $count++;
        }
      }

      // The array is in the correct form.
      try {
        $this->buildWhereClause($args[0], $method == 'where' ? 'AND' : 'OR');
      } catch (Exception $e) {
        throw $e;
      }
    } elseif (is_scalar($args[0])) {
      // Method was called with a list of arguments.
      // Check if the arguments are all scalar, and if they are two or three.
      if (count($args) < 2 || count($args) > 3)
        throw new RuntimeException('Arguments passed to `Query::'.$method.'()` aren\'t in the correct form');
        $count = 0;
      foreach ($args as $clause) {
        if (!is_scalar($clause) && (!is_array($clause) || $count != 3))
          throw new RuntimeException('Arguments passed to `Query::'.$method.'()` aren\'t in the correct form');
        $count++;
      }

      // Arguments are in the correct form.
      try {
        $this->buildWhereClause(array($args), $method == 'where' ? 'AND' : 'OR');
      } catch (Exception $e) {
        throw $e;
      }
    } else {
      // Method was called in an incorrect way.
      throw new RuntimeException('Arguments passed to `Query::'.$method.'()` aren\'t in the correct form');
    }

    return $this;
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
   * @throws \RuntimeException if $value elements aren't in the correct form.
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  private function processWhereIn($field, array $values, $method = 'whereIn') {
    if (!is_string($field))
      throw InvArgExcp::typeMismatch('field name', $field, 'String');

    $where = $this->where;
    if ($where != '')
      $where = 'WHERE '.$field.' ';
    else
      $where .= ' AND '.$field.' ';
    if ($method == 'whereNotIn')
      $where .= 'NOT ';
    $where .= 'IN (';

    $count = 0;
    foreach ($values as $value) {
      // Correct $value.
      try {
        $correct = self::correctValue($value, $method);
      } catch (Exception $e) {
        throw $e;
      }

      // Attach conditions to WHERE clause.
      if ($count != 0)
        $where .= ', ';
      $where .= $correct;

      $count++;
    }

    $where .= ')';
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
   * @param array $clauses `WHERE` clauses.
   * @param string $operator `AND` or `OR`.
   *
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  private function buildWhereClause(array $clauses, $operator) {
    $where = $this->where;
    $method = ($operator == 'AND') ? 'where' : 'orWhere';

    foreach ($clauses as $clause) {
      if (!is_array($clause))
        throw new RuntimeException(
          'Arguments passed to `Query::'.$method.'()` aren\'t in the correct form'
        );

      if ($where != '')
        $where = ' '.$operator.' ';
      else
        $where = 'WHERE ';

      if (count($clause) == 2) {
        $value = $clause[1];
        $oprt = '=';
      } elseif (count($clause) == 3) {
        $value = $clause[2];
        $oprt = $clause[1];
        if (!self::isOperatorValid($oprt, 'buildWhereClause'))
          throw new RuntimeException(
            'Arguments passed to `Query::'.$method.'()` aren\'t in the correct form'
          );
      }

      if (is_array($value) && ($oprt == 'between' || $oprt == 'BETWEEN')) {
        // Replace the value with a string that contains the values in the
        // array, linked by 'AND'.
        // If the array doesn't contains exactly 2 values, raise an exception.
        if (count($value) != 2)
          throw new RuntimeException(
            'Arguments passed to `Query::'.$method.'()` aren\'t in the correct form'
          );
        try {
          $value[0] = self::correctValue($value[0]);
          $value[1] = self::correctValue($value[1]);
        } catch (Exception $e) {
          throw $e;
        }
        $value = $value[0].' AND '.$value[1];
      } else {
        try {
          $value = self::correctValue($value, $method);
        } catch (Exception $e) {
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
   * @static
   *
   * @param mixed $value Scalar value.
   * @param string $method Name of the method that called this one (directly or
   * indirectly).
   * @return mixed The correct value.
   *
   * @throws \RuntimeException if the value is an array.
   */
  private static function correctValue($value, $method) {
    if (is_string($value)) {
      // Put quotes around the value if it is a string.
      $value = "'".$value."'";
    } elseif (is_null($value)) {
      // Replace the value with a string 'NULL' if it is null.
      $value = 'NULL';
    } elseif (is_bool($value)) {
      // Replace the value with a string 'TRUE' or 'FALSE' if it is boolean.
      $value = ($value == true) ? 'TRUE' : 'FALSE';
    } elseif (!is_scalar($value)) {
      throw new RuntimeException(
        'Arguments passed to `Query::'.$method.'()` aren\'t in the correct form'
      );
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
      $valid_operators = array('=', '>', '>=', '<', '<=', '!=', 'like', 'LIKE');
    else
      $valid_operators = array('=', '>', '>=', '<', '<=', '!=', 'between', 'BETWEEN', 'like', 'LIKE');

    return in_array($operator, $valid_operators);
  }

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws \RuntimeException if $table property isn't set or if there is some
   * inconsistency with the data required by every specific method.
   */
  private function build() {
    try {
      switch ($this->type) {
        case 'select':
          return $this->buildSelect();
          break;
        case 'update':
          return $this->buildUpdate();
          break;
        case 'insert':
          return $this->buildInsert();
          break;
        case 'delete':
          return $this->buildDelete();
          break;
      }
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Build SELECT query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws \RuntimeException if $table property isn't set.
   */
  private function buildSelect() {
    // Create the `SELECT` part.
    $query = 'SELECT ';
    if (!is_array($this->select) || empty($this->select)) {
      $part = $this->buildCount();
      if ($part == '')
        $query .= '* ';
      else
        $query .= $part.' ';
    }
    else {
      $count = 0;
      foreach ($this->select as $field) {
        if ($count != 0)
          $query .= ', ';
        $query .= $field;
        $count++;
      }
      $part .= $this->buildCount();
      if ($part != '')
        $query .= ', '.$part;
      $query .= ' ';
    }

    // Append the `FROM` part.
    $query .= 'FROM ';
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');
    $query .= $this->table.' ';

    // Append the `JOIN` part.
    if (!empty($this->joins)) {
      foreach ($this->joins as $join) {
        // $join[0] --> second table.
        // $join[1] --> field one.
        // $join[2] --> operator.
        // $join[3] --> field two.
        // $join[4] --> join type.
        $query .= sprintf(
          '%1$s JOIN %2$s ON %3$s %4$s %5$s ',
          $join[4],
          $join[0],
          $join[1],
          $join[2],
          $join[3]
        );
      }
    }

    // Append the `WHERE` part.
    $query .= $this->where;

    // Append the `GROUP BY` part.
    if (isset($this->group_by)) {
      $query .= ' '.$this->group_by;

      // Append the `HAVING` part.
      if (!empty($this->having)) {
        $query .= sprintf(
          ' HAVING %1$s %2$s %3$s',
          $this->having[0],
          $this->having[1],
          $this->having[2]
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
   * @since 1.0.0
   * @access private
   *
   * @return string
   */
  private function buildCount() {
    $part = '';

    if ($this->count == true || empty($this->count)) {
      $part .= 'COUNT(*)';
    } elseif (is_array($this->count)) {
      $count = 0;
      foreach ($this->count as $field) {
        if ($count != 0)
          $part .= ', ';
        $part .= 'COUNT('.$field.')';
        $count++;
      }
    }

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
   * @throws \RuntimeException if $table and $update properties aren't set.
   */
  private function buildUpdate() {
    // `UPDATE` part.
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');

    $query = 'UPDATE '.$this->table.' ';

    // Append `SET` part.
    if (!isset($this->update) || empty($this->update))
      throw new RuntimeException('Cannot execute an UPDATE query without updating anything');

    $query .= 'SET ';
    $count = 0;
    foreach ($this->update as $field => $value) {
      if ($count != 0)
        $query .= ', ';
      $query .= $field.' = '.$value;
      $count++;
    }

    // Append `WHERE` part.
    $query .= ' '.$this->where;

    // Append `ORDER BY` part.
    $query .= $this->buildOrderBy();

    // Append `LIMIT` part.
    $query .= $this->buildLimit();

    return $query;
  }

  /**
   * Build INSERT query.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws \RuntimeException if $table and $insert properties aren't set.
   */
  private function buildInsert() {
    // `INSERT INTO` part.
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');
    if (!isset($this->insert) || empty($this->insert))
      throw new RuntimeException('Cannot execute an INSERT query without inserting anything');

    $query = 'INSERT INTO '.$this->table.' (';

    // Append fields and values.
    $values = '';
    $count = 0;
    foreach ($this->insert as $field => $value) {
      if ($count != 0) {
        $query .= ', ';
        $values .= ', ';
      }
      $query .= $field;
      $values .= $value;
      $count++;
    }
    $query .= ') VALUES ('.$values.')';

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
   * @throws \RuntimeException if $table property isn't set.
   */
  private function buildDelete() {
    // `DELETE` part.
    if (!isset($this->table))
      throw new RuntimeException('Cannot execute the query without a target table');

    $query = 'DELETE '.$this->delete_modifier.' FROM '.$this->table.' ';

    // Append `WHERE` part.
    $query .= $this->where;

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
    $part = '';

    if (!empty($this->order_by)) {
      $part .= ' ORDER BY ';
      $count = 0;
      foreach ($this->order_by as $field => $direction) {
        if ($count != 0)
          $part .= ', ';
        $part .= $field.$direction;
      }
    }

    return $part;
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
    $part = '';

    if (isset($this->limit)) {
      $part .= ' LIMIT ';
      if (isset($this->offset) && !is_null($this->offset))
        $part .= $this->offset.', ';
      $part .= $this->limit;
    }

    return $part;
  }
}
