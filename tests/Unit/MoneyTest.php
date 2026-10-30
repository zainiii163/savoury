<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    #[DataProvider('majorProvider')]
    public function test_converts_major_pkr_to_minor_units(int|float $major, int $minor): void
    {
        $this->assertSame($minor, Money::toMinor($major));
    }

    /**
     * @return list<array{0: int|float, 1: int}>
     */
    public static function majorProvider(): array
    {
        return [
            [375, 37_500],
            [565, 56_500],
            [465, 46_500],
            [0, 0],
        ];
    }

    public function test_converts_exact_minor_units_back_to_integer_major(): void
    {
        $this->assertSame(375, Money::toMajor(37_500));
    }
}
