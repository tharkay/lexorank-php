<?php

declare(strict_types=1);

namespace AlexCrawford\LexoRank\Tests\Exception;

use AlexCrawford\LexoRank\Exception\InvalidFormat;
use PHPUnit\Framework\TestCase;

/** @covers \AlexCrawford\LexoRank\Exception\InvalidFormat */
class InvalidFormatTest extends TestCase
{
    public function testForFailedRegexMatch(): void
    {
        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage('BucketRank doesn\'t match the required regex "(\d+)\|(.*)" for input "0-U"');
        $this->expectExceptionCode(0);

        throw InvalidFormat::forFailedRegexMatch(
            '0-U',
            '(\d+)\|(.*)',
        );
    }
}
