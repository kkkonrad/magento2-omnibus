<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Api;

interface PromotionDetectorInterface
{
    public function hasActiveDiscount(float $regularPrice, float $effectivePrice): bool;

    public function startsReduction(
        bool $wasDiscounted,
        float $previousEffectivePrice,
        float $regularPrice,
        float $effectivePrice
    ): bool;
}
