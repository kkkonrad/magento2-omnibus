<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Api\PromotionDetectorInterface;

class PromotionDetector implements PromotionDetectorInterface
{
    public function hasActiveDiscount(float $regularPrice, float $effectivePrice): bool
    {
        return $effectivePrice < $regularPrice && !$this->pricesEqual($effectivePrice, $regularPrice);
    }

    public function startsReduction(
        bool $wasDiscounted,
        float $previousEffectivePrice,
        float $regularPrice,
        float $effectivePrice
    ): bool {
        return $this->hasActiveDiscount($regularPrice, $effectivePrice)
            && (!$wasDiscounted || $effectivePrice < $previousEffectivePrice);
    }

    private function pricesEqual(float $first, float $second): bool
    {
        return abs($first - $second) < 0.00005;
    }
}
