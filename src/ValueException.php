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

use at\exceptable\Exception as Exceptable;

/**
 * error cases for Value methods.
 */
class ValueException extends Exceptable {

  /**
   * @type int NO_EXPRESSIONS
   * @type int INVALID_FILTER
   * @type int BAD_CALL_RIPLEY
   * @type int FILTER_FAILURE
   */
  const NO_EXPRESSIONS = (1<<1);
  const INVALID_FILTER = (1<<2);
  const BAD_CALL_RIPLEY = (1<<6);
  const FILTER_FAILURE = (1<<8);

  /** @see exceptableTrait::INFO */
  const INFO = [
    self::NO_EXPRESSIONS => [
      'message' => 'at least one $expression must be provided',
      'severity' => Exceptable::WARNING
    ],
    self::INVALID_FILTER => [
      'message' => 'invalid $type filter',
      'severity' => Exceptable::WARNING,
      'tr_message' => 'invalid $type filter: "{type}"'
    ],
    self::BAD_CALL_RIPLEY => [
      'message' => 'error invoking callable',
      'tr_message' => 'error invoking callable: {__rootMessage__}'
    ],
    self::FILTER_FAILURE => [
      'message' => 'value failed filter',
      'severity' => Exceptable::NOTICE,
      'tr_message' => 'filter "{filter}" failed with value: {value}'
    ]
  ];
}
