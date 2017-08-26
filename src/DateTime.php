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

use DateTimeImmutable,
    DateTimeInterface,
    DateTimeZone;

/**
 * decorates PHP's DateTime.
 */
class DateTime extends DateTimeImmutable {

  /** @type string  there, fixed. */
  const ISO8601 = DATE_ATOM;

  /** @type string  format to use with __toString. */
  protected $_toStringFormat;

  /**
   * convenience factory method.
   * @see DateTime::__construct()
   *
   * @return DateTimeInterface  a new DateTime instance
   */
  public static function create($time = null, $timezone = null) : DateTimeInterface {
    return new self($time, $timezone);
  }

  /**
   * creates a datetime instance from a unix timestamp.
   *
   * @param mixed $time         a unix timestamp, as an integer, float, or string
   * @throws DateTimeException  if time value cannot be interpreted as a unix timestamp
   * @return DateTimeInterface  a new DateTimeInterface instance
   */
  public static function createFromUnixtime($time) : DateTimeInterface {
    if (filter_var($time, FILTER_VALIDATE_FLOAT) === false) {
      throw new DateTimeException(DateTimeException::INVALID_UNIXTIME, ['time' => $time]);
    }

    return new self("@{$time}");
  }

  /**
   * interprets integers/floats as unix timestamps (with microsecond support).
   * UTC is assumed if no timezone or offset is specified.
   *
   * note that for compatibility reasons,
   * _string_ representations of ints/floats are not interpreted as unix timestamps
   * (use DateTime::createFromUnixtime() instead).
   *
   * @param mixed $time      unix timestamp (int|float) or datetime string
   * @param mixed $timezone  datetimezone string or instance
   */
  public function __construct($time = 'now', $timezone = null) {
    if (is_int($time) || is_float($time)) {
      $time = "@{$time}";
    }
    if (strpos($time, '@') === 0 && strpos($time, '.')) {
      if (filter_var(substr($time, 1), FILTER_VALIDATE_FLOAT) === false) {
        throw new DateTimeException(DateTimeException::INVALID_UNIXTIME, ['time' => $time]);
      }

      list($U, $u) = explode('.', substr($time, 1));
      $time = date('Y-m-d\TH:i:s', intval($U)) . ".{$u}+0000";
    }

    $timezone = $timezone ?? 'UTC';
    if (is_string($timezone)) {
      $timezone = new DateTimeZone($timezone);
    }

    parent::__construct($time, $timezone);
  }

  /**
   * defaults to (real) iso-8601 format for string representation.
   */
  public function __toString() {
    return $this->format($this->_toStringFormat ?? self::ISO8601);
  }

  /**
   * sets the format used by __toString().
   *
   * @param string $format  the format to use
   * @return $this
   */
  public function setToStringFormat(string $format) : DateTimeInterface {
    $this->_toStringFormat = $format;
    return $this;
  }
}
