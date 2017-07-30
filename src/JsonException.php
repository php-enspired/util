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
 * represents error cases in encoding/decoding json data.
 *
 * preferred usage is to omit the $message and $code;
 * they will be retrieved from json_last_error() and json_last_error_msg().
 */
class JsonException extends Exceptable {

  /** @see Exceptable::INFO */
  const INFO = [
    JSON_ERROR_NONE => ['message' => 'no error has occurred'],
    JSON_ERROR_DEPTH => ['message' => 'the maximum stack depth has been exceeded'],
    JSON_ERROR_STATE_MISMATCH => ['message' => 'invalid or malformed JSON'],
    JSON_ERROR_CTRL_CHAR =>
      ['message' => 'control character error, possibly incorrectly encoded'],
    JSON_ERROR_SYNTAX => ['message' => 'syntax error'],
    JSON_ERROR_UTF8 =>
      ['message' => 'malformed UTF-8 characters, possibly incorrectly encoded'],
    JSON_ERROR_RECURSION =>
      ['message' => 'one or more recursive references in the value to be encoded'],
    JSON_ERROR_INF_OR_NAN =>
      ['message' => 'one or more NAN or INF values in the value to be encoded'],
    JSON_ERROR_UNSUPPORTED_TYPE =>
      ['message' => 'a value of a type that cannot be encoded was given'],
    JSON_ERROR_INVALID_PROPERTY_NAME =>
      ['message' => 'a property name that cannot be encoded was given'],
    JSON_ERROR_UTF16 =>
      ['message' => 'malformed UTF-16 characters, possibly incorrectly encoded']
  ];

  /** @type bool  does this exception represent the most recent json error? */
  private $_lastError = false;

  /** @see Exceptable::_makeCode() */
  protected function _makeCode() : int {
    $this->_lastError = true;
    return json_last_error();
  }

  /** @see Exceptable::_makeMessage() */
  protected function _makeMessage() : int {
    $message = $this->_lastError ?
      json_last_error_msg() :
      static::get_info($this->_code)['message'];

    if (isset($this->_context['json'])) {
      $message .= "\n json: {$this->_context['json']}";
    }
    if (isset($this->_context['data'])) {
      $message .= "\n data: " . serialize($this->_context['data']);
    }
    if (isset($this->_context['opts'])) {
      $message .= "\n opts: " . Json::encode($opts, [Json::PRETTY]);
    }

    return $message;
  }
}
