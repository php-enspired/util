<?php
/**
 * @package    at.util
 * @version    0.4
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

use at\util\Vars;

/**
 * wrapper for pcre functions.
 * @todo: decide how much error handling/ stupid handling to implement
 */
class Regex {

  /**
   * @see <http://php.net/pcre.constants>
   *
   * @const int $PATTERN_ORDER
   * @const int $SET_ORDER
   * @const int $OFFSET_CAPTURE
   * @const int $SPLIT_NO_EMPTY
   * @const int $SPLIT_DELIM_CAPTURE
   * @const int $SPLIT_OFFSET_CAPTURE
   */
  const PATTERN_ORDER = PREG_PATTERN_ORDER;
  const SET_ORDER = PREG_SET_ORDER;
  const OFFSET_CAPTURE = PREG_OFFSET_CAPTURE;
  const SPLIT_NO_EMPTY = PREG_SPLIT_NO_EMPTY;
  const SPLIT_DELIM_CAPTURE = PREG_SPLIT_DELIM_CAPTURE;
  const SPLIT_OFFSET_CAPTURE = PREG_SPLIT_OFFSET_CAPTURE;

  /**
   * @see <http://php.net/reference.pcre.pattern.modifiers>
   *
   * @const int $ANCHORED
   * @const int $CASELESS
   * @const int $DOLLAR_ENDONLY
   * @const int $DOTALL
   * @const int $EXTENDED
   * @const int $EXTRA
   * @const int $INFO_JCHANGED
   * @const int $MATCH_ALL
   * @const int $MULTILINE
   * @const int $UNGREEDY
   * @const int $UTF8
   */
  const ANCHORED = 1;
  const CASELESS = 1<<1;
  const DOLLAR_ENDONLY = 1<<2;
  const DOTALL = 1<<3;
  const EXTENDED = 1<<4;
  const EXTRA = 1<<5;
  const INFO_JCHANGED = 1<<6;
  const MATCH_ALL = 1<<7;
  const MULTILINE = 1<<8;
  const UNGREEDY = 1<<9;
  const UTF8 = 1<<10;

  /**
   * @const array  modifier letter => class constant map. */
  const MODIFIERS = [
    'A' => self::ANCHORED,
    'i' => self::CASELESS,
    'D' => self::DOLLAR_ENDONLY,
    's' => self::DOTALL,
    'x' => self::EXTENDED,
    'X' => self::EXTRA,
    'J' => self::INFO_JCHANGED,
    'g' => self::MATCH_ALL,
    'm' => self::MULTILINE,
    'U' => self::UNGREEDY,
    'u' => self::UTF8
  ];

  /**
   * @const array  error code => description map. */
  const ERROR = [
    PREG_NO_ERROR => 'PREG_NO_ERROR',
    PREG_INTERNAL_ERROR => 'PREG_INTERNAL_ERROR',
    PREG_BACKTRACK_LIMIT_ERROR => 'PREG_BACKTRACK_LIMIT_ERROR',
    PREG_RECURSION_LIMIT_ERROR => 'PREG_RECURSION_LIMIT_ERROR',
    PREG_BAD_UTF8_ERROR => 'PREG_BAD_UTF8_ERROR',
    PREG_BAD_UTF8_OFFSET_ERROR => 'PREG_BAD_UTF8_OFFSET_ERROR',
    PREG_JIT_STACKLIMIT_ERROR => 'PREG_JIT_STACKLIMIT_ERROR'
  ];

  /**
   * factory: creates an instance from a regular expression.
   *
   * @param string $regex              the regular expression (including delimiters + modifiers)
   * @throws InvalidArgumentException  if regular expression is not valid
   * @return Regex                     a Regex instance on success
   */
  public static function from_string(string $regex) : Regex {
    switch ($regex[0]) {
      case '(': $close = ')';
        break;
      case '{': $close = '}';
        break;
      case '[': $close = ']';
        break;
      case '<': $close = '>';
        break;
      default:  $close = $regex[0];
        break;
    }
    $closePosition = strrpos($regex, $close);
    $pattern = substr($regex, 1, ($closePosition - 1));
    $modifiers = substr($regex, ($closePosition + 1));

    return new self($pattern, $modifiers);
  }

  /**
   * performs a search and replace, mapping several patterns onto a subject string.
   *
   * @param string  $subject  the subject string
   * @param array   $map      pattern => replacement map
   * @param int     $limit    maximum number of replacements to perform per subject
   * @return string           the searched+replaced string
   */
  public static function map_replace(string $subject, array $map, int $limit=-1) : string {
    foreach ($map as $pattern=>$replacement) {
      $subject = self::from_string($pattern)->replace($subject, $replacement, $limit);
    }
    return $subject;
  }

  /**
   * escapes characters with special meaning in pcre.
   *
   * @param string $literal  the string to escape
   * @return string          the escaped string
   */
  public static function quote(string $literal) : string {
    return preg_quote($literal);
  }

  /**
   * checks whether a string is a valid regular expression.
   *
   * @param string $pattern  the regular expression to validate
   * @param string &$error   if pattern is invalid, will contain the specific error message
   * @return bool            true if regex is valid; false otherwise
   */
  public static function is_valid(string $pattern, &$error='') : bool {
    if (@preg_match($pattern, '') === false) {
      $error = self::_last_error();
      return false;
    }
    return true;
  }

  /**
   * checks whether a string is a valid regular expression pattern.
   *
   * @param string $pattern  the pattern to validate (sans delimiters + modifiers)
   * @param string &$error   if pattern is invalid, will contain the specific error message
   * @return bool            true if pattern is valid; false otherwise
   */
  public static function is_pattern(string $pattern, &$error='') : bool {
    return self::is_valid("({$pattern})", $error);
  }

  /**
   * checks if the last error was triggered by a preg_* function; gets the message if so.
   *
   * @return string|null  the error message if a preg_* error; null otherwise.
   */
  protected static function _last_error() {
    return self::ERROR[preg_last_error()];
  }

  /**
   * @type bool  perform a global pattern match? */
  protected $_matchAll = false;

  /**
   * @type array  $_modifiers  pattern modifiers
   * @type string $_pattern    pattern
   */
  protected $_modifiers = [];
  protected $_pattern;

  /**
   * @param string     $pattern    regular expression (sans delimiters + modifiers)
   * @param string|int $modifiers  pattern modifiers
   *  (string of literal pcre modifiers or disjunction of self::MOD values)
   */
  public function __construct(string $pattern, $modifiers='u') {
    Vars::typeHint($modifiers, ['string', 'int']);

    $this->_parseModifiers($modifiers);
    $this->_pattern = $pattern;

    if (! self::valid($this->__toString(), $error)) {
      throw new \InvalidArgumentException($error, E_WARNING);
    }
  }

  /**
   * @see <http://php.net/__toString> */
  public function __toString() {
    return "({$this->_pattern})" . implode($this->_modifiers);
  }

  /**
   * performs a pattern match against a given string.
   * @see <http://php.net/preg_match>
   * @see <http://php.net/preg_match_all>
   *
   * @param string $subject  the subject string
   * @param int    $flags    Regex::OFFSET_CAPTURE
   * @param int    $offset   byte offset to start matching against subject string
   * @return string[]        a (possibly empty) array of matches
   */
  public function match(string $subject, int $flags=0, int $offset=0) : array {
    $match = ($this->_matchAll) ? 'preg_match_all' : 'preg_match';
    $match($this->__toString(), $subject, $matches, $flags, $offset);
    return $matches;
  }

  /**
   * checks if a given string matches this pattern.
   *
   * @param string $subject  the subject string
   * @param int    $flags    PREG_OFFSET_CAPTURE
   * @param int    $offset   byte offset to start matching against subject string
   * @return bool            true if string matches pattern; false otherwise
   */
  public function matches(string $subject, int $flags=0, int $offset=0) : bool {
    return ! empty($this->match($subject, $flags, $offset));
  }

  /**
   * performs a search and replace on a given string.
   * @see <http://php.net/preg_replace>
   * @see <http://php.net/preg_replace_callback>
   *
   * @param string          $subject      the subject string
   * @param string|callable $replacement  the replacement string(s) or callback(s)
   * @param int             $limit        maximum number of replacements to perform
   * @throws BadFunctionCallException     if a callback throws or does not return a string
   * @return string                       the result string
   */
  public function replace(string $subject, $replacement, int $limit=-1) : string {
    Vars::typeHint($replacement, ['callable', 'string']);

    $replace = (is_callable($replacement)) ? 'preg_replace_callback' : 'preg_replace';
    return $replace($this->__toString(), $replacement, $subject, $limit);
  }

  /**
   * splits subject into substrings.
   * @see <http://php.net/preg_split>
   *
   * @param string $subject  the subject string
   * @param int    $limit    maximum number of substrings to split subject into
   * @param int    $flags    disjunction of Regex::SPLIT_* flags.
   * @return string[]        a list of substring(s).
   */
  public function split(string $subject, int $flags=0, int $limit=-1) : array {
    return preg_split($this->__toString(), $subject, $limit, $flags);
  }

  /**
   * performs a pattern match against multiple subject strings.
   * @see <http://php.net/preg_grep>
   *
   * @param string[] $subjects         the subject strings
   * @param int      $flags            Regex::GREP_INVERT
   * @throws InvalidArgumentException  if any item in $subjects is not a string
   * @return array[]                   a subject => matches map
   */
  public function grep(array $subjects, int $flags=0) : array {
    return preg_grep($this->__toString(), $subjects, $flags);
  }

  /**
   * performs a search and replace on multiple strings.
   * @see <http://php.net/preg_replace>
   * @see <http://php.net/preg_replace_callback>
   *
   * @param string[]        $subjects     the subject strings
   * @param string|callable $replacement  the replacement string or callback
   * @param int             $limit        maximum number of replacements to perform
   * @throws BadFunctionCallException     if a callback throws or does not return a string
   * @return string[]                     list of matched+replaced strings
   */
  public function grepReplace(array $subjects, $replacement, int $limit=-1) : array {
    Vars::typeHint($replacement, ['callable', 'string']);

    $pattern = $this->__toString();

    if (is_callable($replacement)) {
      $results = [];
      foreach ($subjects as $subject) {
        $result = preg_replace_callback($pattern, $replacement, $subject, $limit);
        if ($result !== $subject) {
          $results[] = $result;
        }
      }
      return $results;
    }

    if (is_string($replacement)) {
      return preg_filter($pattern, $replacement, $subjects, $limit);
    }

    $t = gettype($replacement);
    $m = "\$replacement must be a string or callback; [{$t}] provided";
    throw new \TypeError($m, E_WARNING);
  }

  /**
   * parses individual modifiers from a string or integer argument (performs no validation).
   *
   * @param string|int $modifiers  the modifiers to parse
   * @return string                pattern modifiers
   */
  protected function _parseModifiers($modifiers) : string {
    Vars::typeHint($modifiers, 'int', 'string');
    if (is_int($modifiers)) {
      foreach () {}
    }
    if (is_string($modifiers)) {

    }



    if (is_int($modifiers)) {
      return $modifiers;
    }
    $bitmask = 0;
    foreach (str_split($modifiers) as $modifier) {
      if (isset(self::MODIFIERS[$modifier])) {
        $bitmask |= $modifier;
      }
    }
    return $bitmask;
  }
}
