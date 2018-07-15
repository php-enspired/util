<?php
/**
 * @package    at.util
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2018
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
 * Error cases for Value methods.
 */
class ValueException extends Exceptable {

  /**
   * @type int NO_EXPRESSIONS  no expressions were provided for debugging
   */
  const NO_EXPRESSIONS = 1;

  /** @see exceptableTrait::INFO */
  const INFO = [
    self::NO_EXPRESSIONS => [
      'message' => 'at least one $expression must be provided',
      'severity' => Exceptable::WARNING
    ]
  ];
}
