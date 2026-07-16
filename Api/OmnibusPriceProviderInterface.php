<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Api;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;

interface OmnibusPriceProviderInterface
{
    public function get(int $productId, int $websiteId, int $customerGroupId): ?OmnibusPriceInterface;

    /**
     * @param int[] $productIds
     * @return array<int, OmnibusPriceInterface>
     */
    public function getList(array $productIds, int $websiteId, int $customerGroupId): array;
}
