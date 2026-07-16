<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Api;

interface LowestPriceCalculatorInterface
{
    public function calculate(
        int $productId,
        int $websiteId,
        int $customerGroupId,
        string $periodFrom,
        string $periodTo
    ): ?float;
}
