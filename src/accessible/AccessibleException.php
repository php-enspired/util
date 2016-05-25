<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no later versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any later version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\util\accessible;

use at\util\exceptable\api\Exceptable as ExceptableApi,
    at\util\exceptable\exceptable;

class AccessibleException extends \RuntimeException implements ExceptableApi {
  use exceptable;

  /**
   * Exception codes known to this class.
   *
   * @type int ACCESS_ERROR    generic Accessible error
   * @type int INVALID_OFFSET  given offset is invalid
   * @type int INVALID_VALUE   given value is invalid
   */
  const ACCESS_ERROR = 1;
  const INVALID_OFFSET = (1<<1);
  const INVALID_VALUE = (1<<2);

  /**
   * @see exceptable::DEFAULT_CODE */
  const DEFAULT_CODE = self::ACCESS_ERROR;

  /**
   * @see exceptable::INFO */
  const INFO = [
    self::ACCESS_ERROR => [
      'code' => self::ACCESS_ERROR,
      'message' => 'unknown access error',
      'severity' => E_ERROR
    ],
    self::INVALID_OFFSET => [
      'code' => self::INVALID_OFFSET,
      'message' => 'no such offset exists',
      'severity' => E_WARNING,
      'tr' => ['offset' => ''],
      'tr_message' => 'no offset [%offset%] exists'
    ],
    self::INVALID_VALUE => [
      'code' => self::INVALID_VALUE,
      'message' => 'provided value is not valid for offset',
      'severity' => E_WARNING,
      'tr' => ['offset' => '', 'value' => ''],
      'tr_message' => "invalid value provided for offset [%offset%]: \n%value%"
    ]
  ];
}
