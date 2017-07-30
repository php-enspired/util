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

use at\util\ {
  JsonException,
  VarTools
};

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
   * keys for decode/encode $opts tuple.
   *
   * @type int DECODE_ASSOC
   * @type int DECODE_OPTIONS
   * @type int DECODE_DEPTH
   * @type int ENCODE_OPTIONS
   * @type int ENCODE_DEPTH
   */
  const DECODE_ASSOC = 0;
  const DECODE_OPTIONS = 1;
  const DECODE_DEPTH = 2;
  const ENCODE_OPTIONS = 0;
  const ENCODE_DEPTH = 1;

  /**
   * wraps json_decode with preferred options and error handling boilerplate.
   *
   * @param string $json       the json string to decode
   * @param array  $opts {
   *    @type bool self::DECODE_ASSOC    decode as associative array?
   *    @type int  self::DECODE_OPTIONS  bitmask of json_decode options
   *    @type int  self::DECODE_DEPTH    recursion limit
   *  }
   * @throws RuntimeException  if json_decode fails
   * @return mixed             the decoded json data on success
   */
  public static function decode(string $json, array $opts=[]) {
    $assoc = $opts[self::DECODE_ASSOC] ?? self::DEFAULT_ASSOC;
    VarTools::typeHint('$opts[Json::DECODE_ASSOC]', $assoc, 'bool');
    $options = $opts[self::DECODE_OPTIONS] ?? self::DEFAULT_DECODE_OPTIONS;
    VarTools::typeHint('$opts[Json::DECODE_OPTIONS]', $options, 'int');
    $depth = $opts[self::DECODE_DEPTH] ?? self::DEFAULT_DEPTH;
    VarTools::typeHint('$opts[Json::DECODE_DEPTH]', $depth, 'int');

    $value = json_decode($json, $assoc, $depth, $options);
    if (json_last_error() === JSON_ERROR_NONE) {
      return $value;
    }

    throw new JsonException(['json' => $json, 'opts' => $opts]);
  }

  /**
   * wraps json_encode with preferred options and error handling boilerpate.
   * @see <http://php.net/json_encode>
   *
   * @param mixed $data  the data to encode
   * @param array $opts  {
   *    @type int self::ENCODE_OPTIONS  bitmask of json_encode options
   *    @type int self::ENCODE_DEPTH    recursion limit
   *  }
   * @throws RuntimeException  if json_encode fails
   * @return string            the encoded json string on success
   */
  public static function encode($data, array $opts): string {
    $options = $opts[self::ENCODE_OPTIONS] ?? self::DEFAULT_ENCODE_OPTIONS;
    VarTools::typeHint('$opts[Json::ENCODE_OPTIONS]', $options, 'int');
    $depth = $opts[self::ENCODE_DEPTH] ?? self::DEFAULT_DEPTH;
    VarTools::typeHint('$opts[Json::ENCODE_DEPTH]', $depth, 'int');

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
   * @param string|null &$error  filled with error message if json is invalid; null otherwise
   * @return bool                true if value is valid json; false otherwise
   */
  public static function isValid($value, &$error = null) {
    if (is_string($value)) {
      try {
        self::decode($value);
        $error = null;
        return true;
      } catch (JsonException $e) {
        $error = $e->getMessage();
      }
    }
    return false;
  }
}
