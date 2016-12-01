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

use at\util\JsonException;

/**
 * wraps JSON functions with sensible defaults and error handling boilerplate.
 */
class Json {

  /**
   * @type bool DEFAULT_ASSOC           prefer decoding data as arrays.
   * @type int  DEFAULT_DECODE_OPTIONS  preferred options for json_decode.
   * @type int  DEFAULT_ENCODE_OPTIONS  preferred options for json_encode.
   * @type int  DEFAULT_DEPTH           default depth.
   * @type int  HEX                     all JSON_HEX_* options.
   * @type int  PRETTY                  default encoding options + pretty printing.
   */
  const DEFAULT_ASSOC = true;
  const DEFAULT_DECODE_OPTIONS = JSON_BIGINT_AS_STRING;
  const DEFAULT_ENCODE_OPTIONS = JSON_BIGINT_AS_STRING |
    JSON_PRESERVE_ZERO_FRACTION |
    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE;
  const DEFAULT_DEPTH = 512;
  const HEX = JSON_HEX_QUOT |
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS;
  const PRETTY = self::DEFAULT_ENCODE_OPTIONS | JSON_PRETTY_PRINT;

  /**
   * wraps json_decode with preferred options and error handling boilerplate.
   *
   * @param string $json       the json string to decode
   * @param array  $opts {
   *    @type bool $assoc|$0    decode as associative array?
   *    @type int  $options|$1  bitmask of json_decode options
   *    @type int  $depth|$2    recursion limit
   *  }
   * @throws RuntimeException  if json_decode fails
   * @return mixed             the decoded json data on success
   */
  public static function decode(string $json, array $opts=[]) {
    $assoc = $opts['assoc'] ?? $opts[0] ?? self::DEFAULT_ASSOC;
    Vars::typeHint($assoc, 'bool', '$opts[assoc]');
    $options = $opts['options'] ?? $opts[1] ?? self::DEFAULT_DECODE_OPTIONS;
    Vars::typeHint($options, 'int', '$opts[options]');
    $depth = $opts['depth'] ?? $opts[2] ?? self::DEFAULT_DEPTH;
    Vars::typeHint($depth, 'int', '$opts[depth]');

    $value = json_decode($json, $assoc, $depth, $options);
    if (json_last_error === JSON_ERROR_NONE) {
      return $value;
    }

    throw new JsonException(['json' => $json, 'opts' => $opts]);
  }

  /**
   * wraps json_encode with preferred options and error handling boilerpate.
   * @see <http://php.net/json_encode>
   *
   * @param mixed $data        the data to encode
   * @param array $opts {
   *    @type int $options|$0  bitmask of json_encode options
   *    @type int $depth|$1    recursion limit
   *  }
   * @throws RuntimeException  if json_encode fails
   * @return string            the encoded json string on success
   */
  public static function encode($data, array $opts): string {
    $options = $opts['options'] ?? $opts[0] ?? self::DEFAULT_ENCODE_OPTIONS;
    Vars::typeHint($options, 'int', '$opts[options]');
    $depth = $opts['depth'] ?? $opts[1] ?? self::DEFAULT_DEPTH;
    Vars::typeHint($depth, 'int', '$opts[depth]');

    $json = json_encode($data, $options, $depth);
    if (json_last_error() === JSON_ERROR_NONE) {
      return $json;
    }

    throw new JsonException(['data' => $data, 'opts' => $opts]);
  }

  /**
   * checks whether value is a valid json string.
   *
   * @param mixed       $value   the value to check
   * @param string|null &$error  filled with error message, if invalid; null otherwise
   * @return bool                true if value is valid json; false otherwise
   */
  public static function is_valid($value, &$error = null) {
    try {
      self::decode($value);
      $error = null;
      return true;
    } catch (JsonException $e) {
      $error = $e->getMessage();
      return false;
    }
  }
}
