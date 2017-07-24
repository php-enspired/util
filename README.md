util
====

Various PHP utility classes and interfaces/traits for general use.

* ### ArrayTools

Utility functions for arrays, such as `cetegorize()`, `dig()`, `extend()`, and `rekey()`.

Also proxies many built-in php array functions, and removes the by-reference behavior found on the `*sort()` functions and some others.

* ### DateTimeable

Extends the `DateTime` classes to accept floats/integers as unix timestamps, with microsecond support.

* ### Json

Wraps JSON functions with sensible defaults and exception-based error handling.  Extends the `jsonSerializable` interface with `toArray()` and `toJson()`.

* ### PDO

Sets more secure default options for PDO, and provides convenience methods for preparing+executing a query in one step, and for dynamically generating parameter markers for an array of values.

* ### Validator

Methods for validating common conditions (comparisons, sizes/ranges, lists, pattern matching) as well as structures for conditionally applying sets of rules.

* ### VarTools

Utility functions for general variable handling, such as type checking and filtering.

dependencies
------------

Requires:

* php 7.0 or later
* Exceptable 1.1 or later (`composer require php-enspired/exceptable`)

installation
------------

_util_ is currently **unreleased**.  It is **NOT FOR PRODUCTION USE**.

I don't expect much to change before release, but until then, you'll have to clone it and manually `composer install` if you want to play around with it.

tests
-----

Run tests with `composer test:unit`.  Current tests cover `ArrayTools`, `DateTimeable`, `PDO`, and `Validator`.

contributing or getting help
----------------------------

I'm on [Freenode at `#php-enspired`](http://webchat.freenode.net?channels=%23php-enspired&uio=d4), or open an issue [on github](https://github.com/php-enspired/util/issues).  Feedback is welcomed as well.
