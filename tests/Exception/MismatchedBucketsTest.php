<?php

declare(strict_types=1);

namespace AlexCrawford\LexoRank\Tests\Exception;

use AlexCrawford\LexoRank\Exception\MismatchedBuckets;
use PHPUnit\Framework\TestCase;

/** @covers \AlexCrawford\LexoRank\Exception\MismatchedBuckets */
class MismatchedBucketsTest extends TestCase
{
    public function testForMismatchedBuckets(): void
    {
        $this->expectException(MismatchedBuckets::class);
        $this->expectExceptionMessage('BucketRanks are of two different buckets: 0 and 1');
        $this->expectExceptionCode(0);

        throw MismatchedBuckets::forMismatchedBuckets(0, 1);
    }
}
