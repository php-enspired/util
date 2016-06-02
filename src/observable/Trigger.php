<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no other versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any other version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\util\observable;

use at\util\observable\ObservableException,
    at\util\Regex;
use Ds\Set;

/**
 * aggregates and matches event name patterns.
 *
 * patterns may be provided as:
 *  - regular expressions (sans delimiters, ^$ anchors, and flags)
 *  - literal event names
 *
 */
class Trigger {

  /**
   * @type string  default (wildcard) trigger pattern. */
  const DEFAULT_PATTERN = '.*';

  /**
   * @type Set  collection of patterns that match this trigger. */
  protected $_patterns;

  /**
   * @type Regex  aggregated regular expression that matches this trigger. */
  protected $_regex;

  /**
   */
  public function __construct() {
    $this->_patterns = new Set;
  }

  /**
   * parses and registers given pattern(s) with the trigger.
   *
   * @param string $patterns  pattern(s) to register
   */
  public function add( string ...$patterns ) {
    $this->_regex = null;
    $this->_patterns->add( ...array_map( [$this, '_parse'], $patterns ) );
  }

  /**
   * clears all patterns from this trigger. */
  public function clear() {
    $this->_regex = null;
    $this->_patterns->clear();
  }

  /**
   * checks whether a given event name matches this trigger.
   *
   * @param string $event  the event name to test
   * @return bool          true if event name matches trigger; false otherwise
   */
  public function matches( string $event ) {
    if ( $this->_patterns->isEmpty() ) {
      return false;
    }
    if ( $this->_regex === null ) {
      $aggregateRx = "(?|{$this->_patterns->join( '|' )})";
      $this->_regex = new Regex( $aggregateRx, Regex::MOD['u'] );
    }
    return $this->_regex()->matches( $event );
  }

  /**
   * removes given patterns from trigger.
   *
   * @param string …$patterns  pattern(s) to remove
   */
  public function remove( string ...$patterns ) {
    $this->_regex = null;
    $this->_patterns->remove( ...array_map( [$this, '_parse'], $patterns ) );
  }

  /**
   * parses a string as a trigger regex.
   *
   * @param string $pattern  event name
   * @return string          the parsed pattern
   */
  protected function _parse( string $pattern ) : string {
    return (Regex::valid( $pattern )) ?
      $pattern :
      Regex::quote( $pattern );
  }
}
