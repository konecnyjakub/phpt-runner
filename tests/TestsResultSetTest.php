<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("Tests result set")]
final class TestsResultSetTest extends TestCase
{
    public function testConstructor(): void
    {
        $results = [
            new FileResultSet("", "", "", Outcome::Passed, "", ""),
            new FileResultSet("", "", "", Outcome::Skipped, "", ""),
            new FileResultSet("", "", "", Outcome::Failed, "", ""),
            new FileResultSet("", "", "", Outcome::Passed, "", ""),
            new FileResultSet("", "", "", Outcome::Failed, "", ""),
            new FileResultSet("", "", "", Outcome::Passed, "", ""),
        ];
        $testsResultSet = new TestsResultSet($results);

        $this->assertSame(6, $testsResultSet->testsTotal);
        $this->assertSame(3, $testsResultSet->testsPassed);
        $this->assertSame(1, $testsResultSet->testsSkipped);
        $this->assertSame(2, $testsResultSet->testsFailed);
    }
}
