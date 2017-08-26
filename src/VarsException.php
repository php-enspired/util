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
 * error cases for Vars methods.
 */
class VarsException extends Exceptable {

  /**
   * @type int NO_EXPRESSIONS
   * @type int INVALID_FILTER
   * @type int UNCASTABLE
   * @type int BAD_CALL_RIPLEY
   * @type int INVALID_TIME_VALUE
   */
  const NO_EXPRESSIONS = (1<<1);
  const INVALID_FILTER = (1<<2);
  const UNCASTABLE = (1<<4);
  const BAD_CALL_RIPLEY = (1<<6);
  const INVALID_TIME_VALUE = (1<<7);

  /** @see exceptableTrait::INFO */
  const INFO = [
    self::NO_EXPRESSIONS => [
      'message' => 'at least one $expression must be provided'
    ],
    self::INVALID_FILTER => [
      'message' => 'invalid filter definition',
      'severity' => Exceptable::WARNING,
      'tr_message' => 'invalid filter definition: {definition}'
    ],
    self::UNCASTABLE => [
      'message' => 'uncastable value',
      'severity' => Exceptable::WARNING,
      'tr_message' => 'value of type "{value}" cannot be cast to {type}'
    ],
    self::BAD_CALL_RIPLEY => [
      'message' => 'error invoking callable',
      'tr_message' => 'error invoking callable: {__rootMessage__}'
    ],
    self::INVALID_TIME_VALUE => [
      'message' => 'invalid date/time value',
      'severity' => Exceptable::WARNING,
      'tr_message' => '"{value}" is not a valid date/time value',
    ]
  ];
}
