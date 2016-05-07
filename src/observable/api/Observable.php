<?php
/**
 * @package    at.util
 * @version    0.4[20160424]
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GNU GPL V2 <http://gnu.org/licenses/gpl-2.0.txt>
 */
declare( strict_types = 1 );
namespace at\util\observable\api;

/**
 * a refinement of SplSubject.
 * provides for named events, filtered subscriptions, and passing arbitrary data with updates.
 *
 * implementations MUST be completely interoperable with the native SplSubject/SplObserver interfaces.
 *
 * implementations should document what events they send notifications on.
 * the following annotation format is recommended:
 *    '@observable "<event name>"( <update argument list> )'
 *
 * event names are arbitrary.
 * it is suggested that event names be comprised of "word" characters (\w),
 * thereby allowing for name hierarchies delimited by non-word characters (\W).
 */
interface Observable extends \SplSubject {

  /**
   * @see <http://php.net/SPLSubject.attach>
   * in addition:
   *
   * observers may subscribe to specific events by passing a "trigger" as the second arg.
   * triggers are either regular expressions or literal strings.
   * if omitted, the observer will recieve updates for all events.
   *
   * if an observer is already registered, triggers are added to the existing trigger list.
   * to replace the existing list instead, pass FALSE as the third arg.
   *
   * @param SPLObserver     $observer  the observer to register
   * @param string|string[] $1         list of triggers (regexes) to update observer on
   * @param bool            $2         append to existing trigger list (defaults to TRUE)?
   */
  public function attach( \SplObserver $observer );

  /**
   * @see <http://php.net/SPLSubject.detach>
   * in addition:
   *
   * to remove specific triggers, pass them as the second arg.
   * if omitted, or if the observer's trigger list is emptied, the observer is detached.
   *
   * @param SplObserver  $observer  the observer to unregister (events from)
   * @param array|string $1         list of event names to remove
   */
  public function detach( \SplObserver $observer );

  /**
   * @see <http://php.net/SPLSubject.notify>
   * in addition:
   *
   * and event name may be passed as the first arg,
   * which will cause updates to be sent only to observers with matching triggers.
   * if omitted, all observers will recieve updates.
   *
   * @param string $0  event name
   * @param mixed  $…  additional args to pass to each observer
   */
  public function notify();
}
