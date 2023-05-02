<?php

declare(strict_types=1);

namespace AlexCrawford\LexoRank\Exception;

use InvalidArgumentException;

class MismatchedBuckets extends InvalidArgumentException
{
    public static function forMismatchedBuckets(int $firstBucket, int $secondBucket): self
    {
        return new self('BucketRanks are of two different buckets: ' . $firstBucket . ' and ' . $secondBucket);
    }
}
