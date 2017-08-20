![](https://img.shields.io/badge/%E2%9A%A0-unreleased-red.svg?colorA=e05d44&colorB=e05d44)  ![](https://img.shields.io/badge/PHP-7.0-blue.svg?colorB=8892BF)  ![](https://img.shields.io/badge/license-GPL_3.0_only-blue.svg)

util
====

Various PHP utility classes and interfaces/traits for general use.

* ### Arrays

Utility functions for arrays; also proxies many built-in php array functions, and removes the by-reference behavior found on the `*sort()` functions and some others.

* ### DateTime

Extends the `DateTime` classes to accept floats/integers as unix timestamps, with microsecond support.

* ### Json

Wraps JSON functions with sensible defaults and exception-based error handling.

* ### PDO

Sets more secure default options for PDO, and provides a few convenience methods.

* ### Validator

Methods for validating common conditions (comparisons, sizes/ranges, lists, pattern matching) as well as structures for conditionally applying sets of rules.

* ### Vars

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
