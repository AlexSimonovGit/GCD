<?php

declare(strict_types=1);

namespace AlexSimonovGit\GCD\Model;

function gcd(int $a, int $b): int
{
    $a = abs($a);
    $b = abs($b);

    while ($b !== 0) {
        [$a, $b] = [$b, $a % $b];
    }

    return $a;
}

function makeRound(int $min = 1, int $max = 100): array
{
    $a = random_int($min, $max);
    $b = random_int($min, $max);

    return [
        'a' => $a,
        'b' => $b,
        'correct' => gcd($a, $b),
    ];
}