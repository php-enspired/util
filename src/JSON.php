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
namespace at\util;

/**
 * wraps JSON functions with sensible defaults and error handling boilerplate. */
class JSON {

  /**
   * @type bool DEFAULT_ASSOC           decode maps as arrays?
   * @type int  DEFAULT_DECODE_OPTIONS  preferred options for json_decode
   * @type int  DEFAULT_ENCODE_OPTIONS  preferred options for json_encode
   * @type int  DEFAULT_DEPTH           default depth
   * @type int  HEX_ALL                 all JSON_HEX_* options
   * @type int  PRETTY                  default encoding options + pretty printing
   */
  const DEFAULT_ASSOC = true;
  const DEFAULT_DECODE_OPTIONS = JSON_BIGINT_AS_STRING;
  const DEFAULT_ENCODE_OPTIONS = JSON_BIGINT_AS_STRING
    | JSON_PRESERVE_ZERO_FRACTION
    | JSON_UNESCAPED_SLASHES
    | JSON_UNESCAPED_UNICODE;
  const DEFAULT_DEPTH = 512;
  const HEX_ALL = JSON_HEX_QUOT
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS;
  const PRETTY = self::DEFAULT_ENCODE_OPTIONS
    | JSON_PRETTY_PRINT;

  /**
   * wraps json_decode with preferred options and error handling boilerplate.
   *
   * @param string $json       the json string to decode
   * @param array  $opts {
   *    @type bool $assoc    decode as associative array?
   *    @type int  $depth    recursion limit
   *    @type int  $options  bitmask of json_decode options
   *  }
   * @throws RuntimeException  if json_decode fails
   * @return mixed             the decoded json data on success
   */
  public static function decode( string $json, array $opts=[] ) {
    $assoc = $opts['assoc'] ?? self::DEFAULT_ASSOC;
    $depth = $opts['depth'] ?? self::DEFAULT_DEPTH;
    $options = $opts['options'] ?? self::DEFAULT_DECODE_OPTIONS;

    $value = json_decode( $json, $assoc, $depth, $options );
    self::_json_error_check();
    return $value;
  }

  /**
   * wraps json_encode with preferred options and error handling boilerpate.
   * @see <http://php.net/json_encode>
   *
   * @param mixed $data        the data to encode
   * @param array $opts        options, depth
   * @throws RuntimeException  if json_encode fails
   * @return string            the encoded json string on success
   */
  public static function encode( $data, array $opts ): string {
    $options = $opts['options'] ?? self::DEFAULT_ENCODE_OPTIONS;
    $depth = $opts['depth'] ?? self::DEFAULT_DEPTH;

    $json = json_encode( $data, $options, $depth );
    self::_json_error_check();
    return $json;
  }

  /**
   * checks whether value is a valid json string.
   *
   * @param mixed $value   the value to check
   * @return bool          true if value is valid json; false otherwise
   */
  public static function is_json( $value ) {
    try {
      self::decode( $value );
      return true;
    } catch ( \RuntimeException $e ) {
      return false;
    }
  }

  /**
   * checks for errors encountered during last json_* invocation.
   *
   * @throws RuntimeException  if the last json_* function invocation errored
   */
  private static function _json_error_check() {
    $code = json_last_error();
    if ( $code === JSON_ERROR_NONE ) {
      return;
    }
    throw new \RuntimeException( json_last_error_msg(), $code );
  }
}
