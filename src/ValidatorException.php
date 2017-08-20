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
 * represents error cases in Validator methods.
 */
class ValidatorException extends Exceptable {

  /**
   * @type int BAD_CALL_RIPLEY
   * @type int NO_SUCH_RULE
   * @type int INVALID_TIME_VALUE
   * @type int INVALID_CONDITION
   * @type int INVALID_REGEX
   */
  const BAD_CALL_RIPLEY = (1<<1);
  const NO_SUCH_RULE = (1<<2);
  const INVALID_TIME_VALUE = (1<<3);
  const INVALID_CONDITION = (1<<4);
  const INVALID_REGEX = (1<<5);

  /** {@inheritDoc} @see Exceptable::INFO */
  const INFO = [
    self::BAD_CALL_RIPLEY => [
      'message' => 'error invoking callable',
      'tr_message' => 'error invoking callable: {__rootMessage__}'
    ],
    self::NO_SUCH_RULE => [
      'message' => 'no such rule',
      'tr_message' => 'no rule "{rule}" exists'
    ],
    self::INVALID_TIME_VALUE => [
      'message' => 'invalid time value',
      'severity' => Exceptable::WARNING,
      'tr_message' => 'invalid time value: {time}'
    ],
    self::INVALID_CONDITION => [
      'message' => 'invalid condition',
      'severity' => Exceptable::WARNING,
      'tr_message' => '$if must be boolean or callable; {type} provided'
    ],
    self::INVALID_REGEX => [
      'message' => 'invalid regular expression',
      'severity' => Exceptable::WARNING,
      'tr_message' => '{message}'
    ]
  ];
}
