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

class Regex {

  /**
   * @const int[]  list flags to affect Regex behavior.
   *
   * @see <http://php.net/pcre.constants>
   */
  const MODES = [
    'PATTERN_ORDER' => PREG_PATTERN_ORDER,
    'SET_ORDER' => PREG_SET_ORDER,
    'OFFSET_CAPTURE' => PREG_OFFSET_CAPTURE,
    'SPLIT_NO_EMPTY' => PREG_SPLIT_NO_EMPTY,
    'SPLIT_DELIM_CAPTURE' => PREG_SPLIT_DELIM_CAPTURE,
    'SPLIT_OFFSET_CAPTURE' => PREG_SPLIT_OFFSET_CAPTURE
  ];

  /**
   * @const string[]  allowed modifiers.
   *
   * @see <http://php.net/reference.pcre.pattern.modifiers>
   * additionally supports "MATCH_ALL" ('g') to perform a global match
   */
  const MODIFIERS = [
    'ANCHORED' => 'A',
    'CASELESS' => 'i',
    'DOLLAR_ENDONLY' => 'D',
    'DOTALL' => 's',
    'EXTENDED' => 'x',
    'EXTRA' => 'X',
    'INFO_JCHANGED' => 'J',
    'MATCH_ALL' => 'g',
    'MULTILINE' => 'm',
    'UNGREEDY' => 'U',
    'UTF8' => 'u'
  ];

  /**
   * factory: creates an aggregated regex from multiple patterns.
   *
   * @param string …$patterns  the pattern(s) to use
   * @return Regex             the aggregated regex
   */
  public static function aggregate( string ...$patterns ) : Regex {
    return new self( '^(?|' . implode( '|', $patterns ) . ')$' );
  }

  /**
   * escapes characters with special meaning in pcre.
   *
   * @param string $literal  the string to escape
   * @return string          the escaped string
   */
  public static function quote( string $literal ) : string {
    return preg_quote( $literal );
  }

  /**
   * checks whether a string is a valid regular expression.
   *
   * @param string $pattern  the pattern to validate (sans delimiters + modifiers)
   * @param string &$error   if pattern is invalid, will contain the specific error message
   * @return bool            true if pattern is valid; false otherwise
   */
  public static function valid( string $pattern, &$error='' ) : bool {
    if ( @preg_match( "({$pattern})", '' ) === false ) {
      $error = str_replace( 'preg_match(): ', '', error_get_last()['message']);
      return false;
    }
    return true;
  }

  /**
   * @type bool  perform a global pattern match? */
  protected $_matchAll = false;

  /**
   * @type string  the compiled regular expression. */
  protected $_pattern;

  /**
   * @param string   $pattern    regular expression (sans delimiters + modifiers)
   * @param string[] $modifiers  pattern modifiers
   */
  public function __construct( string $pattern, array $modifiers=[] ) {
    // php pcre doesn't understand g
    $g = array_search( self::MODIFIERS['MATCH_ALL'], $modifiers );
    if ( $g !== false ) {
      $this->_matchAll = true;
      unset( $modifiers[$g] );
    }
    $this->_pattern = "({$pattern})" . implode( $modifiers );

    if ( ! Regex::valid( $this->_pattern, $error ) ) {
      throw new \InvalidArgumentException( $error, E_WARNING );
    }
  }

  /**
   * checks if a given string matches this pattern.
   *
   * @param string $subject  the subject string
   * @param int    $flags    PREG_OFFSET_CAPTURE
   * @param int    $offset   byte offset to start matching against subject string
   * @return bool            true if string matches pattern; false otherwise
   */
  public function is_match( string $subject, int $flags=0, int $offset=0 ) : bool {
    return $this->match( $subject, $flags, $offset )->is_match();
  }

  /**
   * performs a pattern match against a given string.
   * @see <http://php.net/preg_match>
   * @see <http://php.net/preg_match_all>
   *
   * @param string $subject  the subject string
   * @param int    $flags    PREG_OFFSET_CAPTURE
   * @param int    $offset   byte offset to start matching against subject string
   * @return PCREResult      a result object
   */
  public function match( string $subject, int $flags=0, int $offset=0 ) : RegexResult {}
}
