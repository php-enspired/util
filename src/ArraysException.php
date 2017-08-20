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
 * error cases for arraytool methods.
 */
class ArraysException extends Exceptable {

  /**
   * @type int NO_SUCH_METHOD
   * @type int INVALID_CATEGORY_KEY
   * @type int INVALID_PATH
   * @type int INVALID_SAMPLE_SIZE
   */
  const NO_SUCH_METHOD = (1<<1);
  const INVALID_CATEGORY_KEY = (1<<2);
  const INVALID_PATH = (1<<3);
  const INVALID_SAMPLE_SIZE = (1<<4);

  /** @see Exceptable::INFO */
  const INFO = [
    self::NO_SUCH_METHOD => [
      'message' => 'no such method',
      'tr_message' => 'no method "Arrays::{method}()" exists'
    ],
    self::INVALID_CATEGORY_KEY => [
      'message' => 'invalid category key',
      'severity' => E_NOTICE,
      'tr_message' => 'invalid category key: "{key}" does not exist in all rows'
    ],
    self::INVALID_PATH => [
      'message' => 'invalid path',
      'severity' => E_NOTICE,
      'tr_message' => 'path "{path}" does not exist in subject array'
    ],
    self::INVALID_SAMPLE_SIZE => [
      'message' => 'invalid sample size',
      'severity' => E_NOTICE,
      'tr_message' => 'sample size must be between 1 and {count}; {size} provided'
    ]
  ];
}
