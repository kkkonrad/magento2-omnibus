<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model;

use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\Config\Source\PercentageMode;
use Kkkonrad\Omnibus\Model\PercentageFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PercentageFormatterTest extends TestCase
{
    #[DataProvider('formatProvider')]
    public function testFormat(string $mode, float $percentage, string $expected): void
    {
        $config = $this->createMock(Config::class);
        $config->expects(self::once())
            ->method('getPercentageMode')
            ->with(3)
            ->willReturn($mode);

        self::assertSame($expected, (new PercentageFormatter($config))->format($percentage, 3));
    }

    public static function formatProvider(): array
    {
        return [
            'discount' => [PercentageMode::DISCOUNT_ONLY, 20.0, '-20%'],
            'increase hidden in discount mode' => [PercentageMode::DISCOUNT_ONLY, -10.0, ''],
            'increase' => [PercentageMode::INCREASE_ONLY, -10.0, '+10%'],
            'hidden' => [PercentageMode::HIDDEN, 20.0, ''],
            'zero' => [PercentageMode::ALL_EXCEPT_ZERO, 0.0, ''],
        ];
    }
}
