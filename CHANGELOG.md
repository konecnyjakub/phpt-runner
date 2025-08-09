Version 0.4.0-dev
- PhptRunner now reports parse error as a failure
- added TestsRunner
- common ini settings can now be set for PhpRunner

Version 0.3.0
- added events to PhptRunner
- section EXPECTHEADERS now forces the use of the cgi binary

Version 0.2.0
- only first line of sections TEST, ARGS and FILE_EXTERNAL is now parsed
- sections GET, COOKIE and PHPDBG are parsed now
- implemented sections EXPECTREGEX, EXPECTREGEX_EXTERNAL, CAPTURE_STDIO, EXPECTF and EXPECTF_EXTERNAL
- tests are marked as flaky/supposed to fail if SKIPIF section has output starting with "flaky" or "xfail" respectively

Version 0.1.0
- initial version
