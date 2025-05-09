PHPT Runner
================

[![Total Downloads](https://poser.pugx.org/konecnyjakub/phpt-runner/downloads)](https://packagist.org/packages/konecnyjakub/phpt-runner) [![Latest Stable Version](https://poser.pugx.org/konecnyjakub/phpt-runner/v/stable)](https://gitlab.com/konecnyjakub/phpt-runner/-/releases) [![build status](https://gitlab.com/konecnyjakub/phpt-runner/badges/master/pipeline.svg?ignore_skipped=true)](https://gitlab.com/konecnyjakub/phpt-runner/-/commits/master) [![coverage report](https://gitlab.com/konecnyjakub/phpt-runner/badges/master/coverage.svg)](https://gitlab.com/konecnyjakub/phpt-runner/-/commits/master) [![License](https://poser.pugx.org/konecnyjakub/phpt-runner/license)](https://gitlab.com/konecnyjakub/phpt-runner/-/blob/master/LICENSE.md)

This library allows running phpt tests. See https://php.github.io/php-src/miscellaneous/writing-tests.html.

Installation
------------

The best way to install PHPT Runner is via Composer. Just add konecnyjakub/phpt-runner to your (dev) dependencies.

Quick start
-----------

To be added

Advanced usage
--------------

To be added

### Supported sections

* --TEST--
* --SKIPIF--
* --STDIN--
* --INI--
* --ARGS--
* --ENV--
* --FILE--
* --XFAIL--
* --FLAKY--
* --EXPECT--
* --CLEAN--

### Implemented but not tested sections
* --FILE_EXTERNAL--
* --EXPECT_EXTERNAL--

### Implemented but not working sections

### Parsed but not implemented sections

* --CONFLICTS--
* --EXTENSIONS--
* --REDIRECTTEST--
* --EXPECTHEADERS--
* --EXPECTREGEX--
* --EXPECTREGEX_EXTERNAL--

### Ignored sections

* --DESCRIPTION--
* --CREDITS--
* --WHITESPACE_SENSITIVE--
* --CAPTURE_STDIO--
* --POST--
* --POST_RAW--
* --PUT--
* --GZIP_POST--
* --DEFLATE_POST--
* --GET--
* --COOKIE--
* --PHPDBG--
* --FILEEOF--
* --CGI--
* --EXPECTF--
* --EXPECTF_EXTERNAL--
