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

use RuntimeException;
use at\exceptable\api\Exceptable,
    at\execptable\exceptable as exceptableTrait;

/**
 * represents error cases in arrays methods.
 */
class VarsException extends RuntimeException implements Exceptable {
  use exceptableTrait;

  /**
   * @type int NO_EXPRESSIONS        no expressions provided
   * @type int INVALID_FILTER        invalid filter definition
   * @type int INVALID_CAST_TYPE     invalid cast type
   * @type int UNCASTABLE            value cannot be cast to type
   * @type int INVALID_CAST_DEFAULT  invalid default value for cast
   */
  const NO_EXPRESSIONS = 1;
  const INVALID_FILTER = (1<<1);
  const INVALID_CAST_TYPE = (1<<2);
  const UNCASTABLE = (1<<3);
  const INVALID_CAST_DEFAULT = (1<<4);

  /** @see exceptableTrait::INFO */
  const INFO = [
    self::NO_EXPRESSIONS => [
      'message' => 'at least one $expression must be provided'
    ],
    self::INVALID_FILTER => [
      'message' => 'invalid filter definition',
      'severity' => E_WARNING,
      'tr_message' => "invalid filter definition: {definition}"
    ],
    self::INVALID_CAST_TYPE => [
      'message' => 'invalid cast type',
      'severity' => E_WARNING,
      'tr_message' => "\$type must be a valid php (pseudo)type; '{type}' provided"
    ],
    self::UNCASTABLE => [
      'message' => 'uncastable value',
      'severity' => E_WARNING,
      'tr_message' => "value of type '{value}' cannot be cast to {type}"
    ],
    self::INVALID_CAST_DEFAULT => [
      'message' => 'invalid cast default',
      'severity' => E_WARNING,
      'tr_message' => "default value must be {type}; {default} provided"
    ]
  ];
}
