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

use at\util\IpException;

/**
 * Utility functions for IP addresses.
 */
class Ip {

  /** @type int  flag for ipv4. */
  public const V4 = 1;

  /** @type int  flag for ipv6. */
  public const V6 = (1 << 1);

  /**
   * @param string $ip  IP address+cidr
   */
  public static function findBroadcast(string $ip) : string {
    if (! self::isAddress($network)) {
      throw new IpException(IpException::INVALID_NETWORK, ['network' => $network]);
    }

  }

  /**
   * @param string $ip  IP address+cidr
   */
  public static function findGateway(string $ip) : string {
    if (! self::isAddress($network)) {
      throw new IpException(IpException::INVALID_NETWORK, ['network' => $network]);
    }

  }

  /**
   * @param string $ip  IP address+cidr
   */
  public static function findNetwork(string $ip) : string {
    if (! self::isAddress($network)) {
      throw new IpException(IpException::INVALID_NETWORK, ['network' => $network]);
    }

  }

  /**
   * @param string $ip  IP address
   */
  public static function inNetwork(string $ip, string $network) : bool {
    if (! self::isValid($ip)) {
      throw new IpException(IpException::INVALID_IP, ['ip' => $ip]);
    }

    if (! self::isAddress($network)) {
      throw new IpException(IpException::INVALID_NETWORK, ['network' => $network]);
    }

  }

  /**
   * @param string $ip  IP address+optional cidr
   */
  public static function isAddress(string $ip) : bool {}

  /**
   * @param string $ip  IP address+optional cidr
   */
  public static function isValid(string $ip) : bool {
    return self::isV4($ip) || self::isV6($ip);
  }

  /**
   * @param string $ip  IP address+optional cidr
   */
  public static function isV4(string $ip) : bool {
    [$ip, $cidr] = self::_parse($ip);
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
      filter_var($cidr ?? 32, FILTER_VALIDATE_INT, ['min_range' => 0, 'max_range' => 32]);
  }

  /**
   * @param string $ip  IP address+optional cidr
   */
  public static function isV6(string $ip) : bool {
    [$ip, $cidr] = self::_parse($ip);
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
      filter_var($cidr ?? 128, FILTER_VALIDATE_INT, ['min_range' => 0, 'max_range' => 128]);
  }

  /**
   * @param string $ip  IP address
   */
  public static function next(string $ip) : string {
    if (! self::isAddress($ip)) {
      throw new IpException(IpException::INVALID_ADDRESS, ['ip' => $ip]);
    }

  }

  /**
   * @param string $ip  IP address+optional cidr
   */
  public static function normalize(string $ip) : string {
    if (! self::isValid($ip)) {
      throw new IpException(IpException::INVALID_IP, ['ip' => $ip]);
    }

    [$ip, $cidr] = self::_parse($ip);
    return inet_ntop(inet_pton($ip)) . "/{$cidr}";
  }

  /**
   * @param string $ip  IP address
   */
  public static function prev(string $ip) : string {
    if (! self::isAddress($ip)) {
      throw new IpException(IpException::INVALID_ADDRESS, ['ip' => $ip]);
    }

  }

  /**
   * Splits an ip into address and cidr parts. Performs no validation.
   *
   * @param string $ip  IP address
   * @return array      address, ?cidr
   */
  protected static function _parse(string $ip) : array {
    return explode('/', $ip, 2) + [1 => null];
  }
}
