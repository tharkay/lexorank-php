<?php

declare(strict_types=1);

namespace AlexCrawford\LexoRank;

use AlexCrawford\LexoRank\Exception\InvalidFormat;
use AlexCrawford\LexoRank\Exception\MismatchedBuckets;

use function count;
use function intval;
use function preg_match;
use function preg_quote;

class BucketRank
{
    public const DEFAULT_FIRST_BUCKET = 0;

    public const DEFAULT_LAST_BUCKET = 2;

    public const DEFAULT_SEPARATOR = '|';

    /**
     * @param non-empty-string $separator
     */
    private function __construct(
        private int $bucket,
        private string $separator,
        private Rank $rank,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function get(): string
    {
        return $this->bucket . $this->separator . $this->rank->get();
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    /**
     * @param non-empty-string $rank
     * @param non-empty-string $separator
     */
    public static function fromString(string $rank, string $separator = self::DEFAULT_SEPARATOR): self
    {
        $regex = '/^(\d+)' . preg_quote($separator) . '(.+)$/';
        preg_match($regex, $rank, $segments);
        if (count($segments) !== 3 || empty($segments[2])) {
            throw InvalidFormat::forFailedRegexMatch($rank, $regex);
        }

        return new self(intval($segments[1]), $separator, Rank::fromString($segments[2]));
    }

    /**
     * @param non-empty-string $separator
     */
    public static function forEmptySequence(int $bucket = self::DEFAULT_FIRST_BUCKET, string $separator = self::DEFAULT_SEPARATOR): self
    {
        return new self($bucket, $separator, Rank::forEmptySequence());
    }

    public static function after(self $prevRank): self
    {
        return new self($prevRank->bucket, $prevRank->separator, Rank::after($prevRank->rank));
    }

    public static function before(self $nextRank): self
    {
        return new self($nextRank->bucket, $nextRank->separator, Rank::before($nextRank->rank));
    }

    public static function betweenRanks(self $prevRank, self $nextRank): self
    {
        if ($prevRank->bucket !== $nextRank->bucket) {
            MismatchedBuckets::forMismatchedBuckets($prevRank->bucket, $nextRank->bucket);
        }

        return new self($prevRank->bucket, $prevRank->separator, Rank::betweenRanks($prevRank->rank, $nextRank->rank));
    }

    public static function withNextBucket(self $current, int $lastBucket = self::DEFAULT_LAST_BUCKET): self
    {
        $nextBucket = $current->bucket >= $lastBucket ? self::DEFAULT_FIRST_BUCKET : $current->bucket + 1;

        return new self($nextBucket, $current->separator, $current->rank);
    }
}
