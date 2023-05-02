<?php

declare(strict_types=1);

namespace AlexCrawford\LexoRank\Exception;

use InvalidArgumentException;

class InvalidFormat extends InvalidArgumentException
{
    public static function forFailedRegexMatch(string $input, string $regex): self
    {
        return new self('BucketRank doesn\'t match the required regex "' . $regex . '" for input "' . $input . '"');
    }
}
