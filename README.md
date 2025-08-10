PHPT Runner
================

[![Total Downloads](https://poser.pugx.org/konecnyjakub/phpt-runner/downloads)](https://packagist.org/packages/konecnyjakub/phpt-runner) [![Latest Stable Version](https://poser.pugx.org/konecnyjakub/phpt-runner/v/stable)](https://gitlab.com/konecnyjakub/phpt-runner/-/releases) [![build status](https://gitlab.com/konecnyjakub/phpt-runner/badges/master/pipeline.svg?ignore_skipped=true)](https://gitlab.com/konecnyjakub/phpt-runner/-/commits/master) [![coverage report](https://gitlab.com/konecnyjakub/phpt-runner/badges/master/coverage.svg)](https://gitlab.com/konecnyjakub/phpt-runner/-/commits/master) [![License](https://poser.pugx.org/konecnyjakub/phpt-runner/license)](https://gitlab.com/konecnyjakub/phpt-runner/-/blob/master/LICENSE.md)

This library allows running phpt tests. The format and possible sections are described on https://php.github.io/php-src/miscellaneous/writing-tests.html.

Installation
------------

The best way to install PHPT Runner is via Composer. Just add konecnyjakub/phpt-runner to your (dev) dependencies.

Quick start
-----------

```php
<?php
declare(strict_types=1);

use Konecnyjakub\PHPTRunner\Parser;
use Konecnyjakub\PHPTRunner\PhpRunner;
use Konecnyjakub\PHPTRunner\PhptRunner;
use Konecnyjakub\PHPTRunner\TestsRunner;

$testsRunner = new TestsRunner(new PhptRunner(new Parser(), new PhpRunner()));
$results = $testsRunner->run(__DIR__ . "/tests");
```

TestsRunner walks through all .phpt files in the given directory (but not subdirectories at the moment), runs them and returns an instance of Konecnyjakub\PHPTRunner\TestsResultSet.

Advanced usage
--------------

To be added

### Fully implemented and tested sections

These sections work exactly as described in the documentation. If you notice any differences, please report them as a bug.

* --TEST--
* --DESCRIPTION--
* --SKIPIF--
* --CAPTURE_STDIO--
* --GET--
* --COOKIE--
* --STDIN--
* --INI--
* --ARGS--
* --ENV--
* --FILE--
* --FILEEOF--
* --FILE_EXTERNAL--
* --CGI--
* --XFAIL--
* --FLAKY--
* --EXPECTHEADERS--
* --EXPECT--
* --EXPECT_EXTERNAL--
* --EXPECTREGEX--
* --EXPECTREGEX_EXTERNAL--
* --CLEAN--

### Partially implemented sections

These sections are used by PhptRunner but there are some differences from behavior described in the documentation. We want to eventually eliminate the differences.

* --EXTENSIONS-- (we do not try to load those extensions, we just skip the test if any of those extensions is not loaded)
* --EXPECTF-- (regular expressions %r...%r are not supported)
* --EXPECTF_EXTERNAL-- (regular expressions %r...%r are not supported)

### Parsed but not implemented sections

These sections are parsed by Parser and returned in ParsedFile but PhptRunner does not use them.

* --CONFLICTS--
* --PHPDBG--
* --REDIRECTTEST--

### Ignored sections

These sections are completely ignored by both Parser and PhptRunner right now. There is no guarantee that they will be (fully) implemented.

* --CREDITS--
* --WHITESPACE_SENSITIVE--
* --POST--
* --POST_RAW--
* --PUT--
* --GZIP_POST--
* --DEFLATE_POST--
