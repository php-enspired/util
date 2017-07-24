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

use InvalidArgumentException,
    DateTime as BaseDateTime,
    DateTimeImmutable as BaseDateTimeImmutable,
    DateTimeInterface,
    DateTimeZone;

/**
 * convenience methods for PHP's DateTime classes.
 */
trait DateTimeable {

  /**
   * convenience factory method.
   * @see DateTimeable::__construct()
   *
   * @return DateTimeInterface  a new DateTimeInterface instance
   */
  public static function create($time = null, $timezone = null) : DateTimeInterface {
    return new self($time, $timezone);
  }

  /**
   * creates a datetime instance from a unix timestamp.
   *
   * @param mixed $time                a unix timestamp, as an integer, float, or string
   * @throws InvalidArgumentException  if time value cannot be interpreted as a unix timestamp
   * @return DateTimeInterface         a new DateTimeInterface instance
   */
  public static function createFromUnixtime($time) : DateTimeInterface {
    if (filter_var($time, FILTER_VALIDATE_FLOAT) === false) {
      $t = is_string($time) ? "'{$time}'" : '(' . gettype($time) . ')';
      throw new InvalidArgumentException("\$time must be a unix timestamp; {$t} provided");
    }

    return new self("@{$time}");
  }

  /**
   * interprets integers/floats as unix timestamps (with microsecond support).
   *
   * note that for compatibility reasons,
   * _string_ representations of ints/floats are not interpreted as timestamps.
   *
   * @param mixed $time      unix timestamp or datetime string
   * @param mixed $timezone  datetimezone string or instance
   */
  public function __construct($time = null, $timezone = null) {
    if (is_int($time) || is_float($time)) {
      $time = "@{$time}";
    }
    if (strpos($time, '@') === 0 && strpos($time, '.')) {
      list($U, $u) = explode('.', substr($time, 1));
      $time = date('Y-m-d\TH:i:s', $U) . ".{$u}+0000";
    }

    parent::__construct($time, $timezone);
  }
}

class DateTime extends BaseDateTime {
  use DateTimeable;
}

class DateTimeImmutable extends BaseDateTimeImmutable {
  use DateTimeable;
}
