<?php
/**
 * @package    at.util
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (only)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  The right to apply the terms of later versions of the GPL is RESERVED.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare(strict_types = 1);

namespace at\util;

use PDO as BasePDO,
    PDOStatement;

/**
 * minor changes and additions to enhance PDO's security and convenience.
 * @see https://3v4l.org/QmVRI
 */
class PDO extends BasePDO {

  /**
   * keys for tuple returned by arrayParam().
   *
   * @type int PARAM_MARKERS
   * @type int PARAM_VALUES
   */
  const PARAM_MARKERS = 0;
  const PARAM_VALUES = 1;

  /**
   * {@inheritDoc}
   * @see http://php.net/PDO.__construct
   *
   * adds good default options.
   */
  public function __construct($dsn, $username = null, $password = null, $options = []) {
    $options += [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    parent::__construct($dsn, $username, $password, $options);
  }

  /**
   * expands arrays into multiple named or positional parameters.
   *
   * @param array  $values  the values to parameterize
   * @param string $named   name for named parameters (omit for positional parameters)
   * @return array          {
   *    @type string self::PARAM_MARKERS  comma-separated parameter markers (sql fragment)
   *    @type array  self::PARAM_VALUES   parameter values, as a map or ordered list
   *  }
   */
  public function arrayParam(array $values, string $named = null) {
    $values = array_values($values);
    $i = 0;
    $keys = array_map(
      function ($value) use ($named, &$i) {
        $marker = $named ? ":{$named}_{$i}" : '?';
        $i++;
        return $marker;
      },
      $values
    );

    if ($named) {
      $values = array_combine($keys, $values);
    }

    return [self::PARAM_MARKERS => implode(', ', $keys), self::PARAM_VALUES => $values];
  }

  /**
   * prepares and executes a statement in one step.
   *
   * @param string $sql     the sql statement to prepare
   * @param array  $params  parameter values to execute against the statement
   * @return PDOStatement   the executed statement object on success
   */
  public function preparedQuery(string $sql, array $params = []) : PDOStatement {
    $stmt = $this->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }
}
