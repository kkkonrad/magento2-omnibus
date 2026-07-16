<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model;

use Kkkonrad\Omnibus\Model\PromotionDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PromotionDetectorTest extends TestCase
{
    private PromotionDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new PromotionDetector();
    }

    #[DataProvider('activeDiscountProvider')]
    public function testHasActiveDiscount(float $regular, float $effective, bool $expected): void
    {
        self::assertSame($expected, $this->detector->hasActiveDiscount($regular, $effective));
    }

    public static function activeDiscountProvider(): array
    {
        return [
            'lower final price' => [100.0, 80.0, true],
            'equal price' => [100.0, 100.0, false],
            'higher final price' => [100.0, 120.0, false],
            'rounding noise' => [100.0, 99.99999, false],
        ];
    }

    public function testNewDiscountStartsReduction(): void
    {
        self::assertTrue($this->detector->startsReduction(false, 100.0, 100.0, 90.0));
    }

    public function testDeeperDiscountStartsAnotherReduction(): void
    {
        self::assertTrue($this->detector->startsReduction(true, 90.0, 100.0, 80.0));
    }

    public function testPriceIncreaseInsideDiscountDoesNotStartReduction(): void
    {
        self::assertFalse($this->detector->startsReduction(true, 80.0, 100.0, 90.0));
    }
}
