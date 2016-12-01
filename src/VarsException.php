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
   * @type int NO_EXPRESSIONS
   * @type int INVALID_FILTER
   */
  const NO_EXPRESSIONS = 1;
  const INVALID_FILTER = (1<<1);

  /** @see exceptableTrait::INFO */
  const INFO = [
    self::NO_EXPRESSIONS => [
      'message' => 'at least one $expression must be provided',
      'severity' => E_WARNING
    ],
    self::INVALID_FILTER => [
      'message' => 'invalid filter definition',
      'severity' => E_WARNING,
      'tr_message' => "invalid filter definition: {definition}",
      'tr' => ['definition' => null]
    ]
  ];
}
