<?php

declare(strict_types=1);

namespace AlexCrawford\LexoRank\Tests;

use AlexCrawford\LexoRank\BucketRank;
use AlexCrawford\LexoRank\Exception\InvalidChars;
use AlexCrawford\LexoRank\Exception\InvalidFormat;
use AlexCrawford\LexoRank\Exception\LastCharCantBeEqualToMinChar;
use AlexCrawford\LexoRank\Exception\MaxRankLength;
use AlexCrawford\LexoRank\Exception\PrevGreaterThanOrEquals;
use AlexCrawford\LexoRank\Rank;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/** @covers \AlexCrawford\LexoRank\BucketRank */
class BucketRankTest extends TestCase
{
    public function testGenerateANewBucketRankFromString(): void
    {
        $rank = BucketRank::fromString('0|AA01');
        self::assertSame('0|AA01', $rank->get());
    }

    /**
     * @param non-empty-string $prev
     * @param non-empty-string $next
     * @param non-empty-string $expected
     *
     * @dataProvider betweenProvider
     */
    public function testBetween(string $prev, string $next, string $expected): void
    {
        $rank = BucketRank::betweenRanks(
            BucketRank::fromString($prev),
            BucketRank::fromString($next),
        );
        self::assertSame($expected, $rank->get());
    }

    /**
     * @return array<array-key, list<non-empty-string>>
     */
    public function betweenProvider(): array
    {
        return [
            'NewDigit' => ['0|aaaa', '0|aaab', '0|aaaaU'],
            'MidValue' => ['1|aaaa', '1|aaac', '1|aaab'],
            'NewDigitMidValue' => ['2|az', '2|b', '2|azU'],
            'NewDigitMidValueSpecialCase' => ['3|amz', '3|ana', '3|amzU'],
            ['4|baba', '4|fgfg', '4|d'],
            ['5|1', '5|2', '5|1U'],
            ['6|ya', '6|ya5', '6|ya2'],
            ['7|ya', '7|yc5', '7|yb'],
        ];
    }

    /**
     * @param non-empty-string $prev
     * @param non-empty-string $expected
     *
     * @dataProvider afterProvider
     */
    public function testAfter(string $prev, string $expected): void
    {
        $rank = BucketRank::after(
            BucketRank::fromString($prev),
        );
        self::assertSame($expected, $rank->get());
    }

    /**
     * @return list<list<non-empty-string>>
     */
    public function afterProvider(): array
    {
        return [
            ['0|aaaa', '0|aaab'],
            ['0|aaaz', '0|aaaz1'],
            ['0|1', '0|2'],
            ['0|y', '0|y1'],
            ['0|x', '0|y'],
        ];
    }

    /**
     * @param non-empty-string $next
     * @param non-empty-string $expected
     *
     * @dataProvider beforeProvider
     */
    public function testBefore(string $next, string $expected): void
    {
        $rank = BucketRank::before(
            BucketRank::fromString($next),
        );
        self::assertSame($expected, $rank->get());
    }

    /**
     * @return list<list<non-empty-string>>
     */
    public function beforeProvider(): array
    {
        return [
            ['0|2', '0|1'],
            ['0|acab', '0|acaa'],
            ['0|aaa1', '0|aaa0y'],
            ['0|2', '0|1'],
            ['0|y1', '0|y0y'],
        ];
    }

    public function testForEmptySequence(): void
    {
        self::assertSame('0|U', BucketRank::forEmptySequence()->get());
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(InvalidFormat::class);
        $this->expectExceptionMessage('BucketRank doesn\'t match the required regex "/^(\d+)\|(.+)$/" for input "1-Ua"');
        BucketRank::fromString('1-Ua');
    }

    public function testInvalidChars(): void
    {
        $this->expectException(InvalidChars::class);
        $this->expectExceptionMessage('Rank provided contains an invalid Char. Rank Provided: 0/0z*z0+0z{z - Invalid char: /, *, +, {');
        BucketRank::fromString('1|0/0z*z0+0z{z');
    }

    public function testMaxBucketRankLength(): void
    {
        $base = str_repeat('y', Rank::MAX_RANK_LEN + 1);

        $this->expectException(MaxRankLength::class);
        BucketRank::fromString('0|' . $base);
    }

    public function testLastCharCantBeEqualToMinChar(): void
    {
        $this->expectException(LastCharCantBeEqualToMinChar::class);
        $this->expectExceptionMessage('The last char of the rank (UUU0) can\'t be equal to the min char (0).');
        BucketRank::fromString('0|UUU0');
    }

    public function testBetweenMaxBucketRankLength(): void
    {
        $base = str_repeat('y', Rank::MAX_RANK_LEN - 1);

        $prev = $base . 'x';
        $next = $base . 'y';

        $this->expectException(MaxRankLength::class);
        $this->expectExceptionMessage('The length of Rank provided is too long. Rank Provided: ' . $prev . 'U - Rank Length: ' . (Rank::MAX_RANK_LEN + 1) . ' - Max length: ' . Rank::MAX_RANK_LEN);
        BucketRank::betweenRanks(
            BucketRank::fromString('0|' . $prev),
            BucketRank::fromString('0|' . $next),
        );
    }

    public function testPrevGreaterThanNext(): void
    {
        $this->expectException(PrevGreaterThanOrEquals::class);
        BucketRank::betweenRanks(
            BucketRank::fromString('0|Z'),
            BucketRank::fromString('0|A'),
        );
    }

    public function testPrevEqualsToNext(): void
    {
        $this->expectException(PrevGreaterThanOrEquals::class);
        BucketRank::betweenRanks(
            BucketRank::fromString('0|D'),
            BucketRank::fromString('0|D'),
        );
    }

    public function testNextBucket(): void
    {
        $rank     = BucketRank::fromString('0|A');
        $nextRank = BucketRank::withNextBucket($rank);

        self::assertSame('0|A', $rank->get());
        self::assertSame('1|A', $nextRank->get());

        $nextRank = BucketRank::withNextBucket($nextRank);
        self::assertSame('2|A', $nextRank->get());

        $nextRank = BucketRank::withNextBucket($nextRank);
        self::assertSame('0|A', $nextRank->get());
    }
}
