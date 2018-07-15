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

use at\util\ {
  JsonException,
  Value
};

/**
 * Wraps JSON functions with sensible defaults and error handling boilerplate.
 */
class Json {

  /**
   * Encode and decode options.
   *
   * @type bool _DEFAULT_ASSOC           prefer decoding data as arrays.
   * @type int  _DEFAULT_DECODE_OPTIONS  preferred options for json_decode.
   * @type int  _DEFAULT_ENCODE_OPTIONS  preferred options for json_encode.
   * @type int  _DEFAULT_DEPTH           default depth.
   *
   * @type int HEX     all JSON_HEX_* options.
   * @type int PRETTY  default encoding options + pretty printing.
   */
  protected const _DEFAULT_ASSOC = true;
  protected const _DEFAULT_DECODE_OPTIONS = JSON_BIGINT_AS_STRING;
  protected const _DEFAULT_ENCODE_OPTIONS = JSON_BIGINT_AS_STRING |
    JSON_PRESERVE_ZERO_FRACTION |
    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE;
  protected const _DEFAULT_DEPTH = 512;

  public const HEX = JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS;
  public const PRETTY = self::DEFAULT_ENCODE_OPTIONS | JSON_PRETTY_PRINT;

  /**
   * Keys for decode/encode $opts tuple.
   *
   * @type int DECODE_ASSOC    decode objects as arrays?
   * @type int DECODE_OPTIONS  decoding options
   * @type int DECODE_DEPTH    maximum recursion level to decode
   * @type int ENCODE_OPTIONS  encoding options
   * @type int ENCODE_DEPTH    maximum recursion level to encode
   */
  public const DECODE_ASSOC = 0;
  public const DECODE_OPTIONS = 1;
  public const DECODE_DEPTH = 2;
  public const ENCODE_OPTIONS = 0;
  public const ENCODE_DEPTH = 1;

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
  public static function decode(string $json, array $opts = []) {
    $assoc = $opts[self::DECODE_ASSOC] ?? self::_DEFAULT_ASSOC;
    Value::hint('$opts[Json::DECODE_ASSOC]', $assoc, Value::ARRAY);

    $depth = $opts[self::DECODE_DEPTH] ?? self::_DEFAULT_DEPTH;
    Value::hint('$opts[Json::DECODE_DEPTH]', $depth, Value::INT);

    $options = $opts[self::DECODE_OPTIONS] ?? self::_DEFAULT_DECODE_OPTIONS;
    Value::hint('$opts[Json::DECODE_OPTIONS]', $options, Value::INT);

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
  public static function encode($data, array $opts = []) : string {
    $options = $opts[self::ENCODE_OPTIONS] ?? self::_DEFAULT_ENCODE_OPTIONS;
    Value::hint('$opts[Json::ENCODE_OPTIONS]', $options, Value::INT);

    $depth = $opts[self::ENCODE_DEPTH] ?? self::_DEFAULT_DEPTH;
    Value::hint('$opts[Json::ENCODE_DEPTH]', $depth, Value::INT);

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
  public static function isValid($value, &$error = null) : bool {
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
