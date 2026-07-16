<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;
use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Kkkonrad\Omnibus\Model\Data\OmnibusPriceFactory;
use Magento\Framework\App\ResourceConnection;
use Kkkonrad\Omnibus\Model\Config\Source\DisplayMode;
use Magento\Framework\Escaper;

class OmnibusPriceProvider implements OmnibusPriceProviderInterface
{
    /** @var array<int, array<int, array<int, OmnibusPriceInterface|null>>> */
    private array $cache = [];

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly OmnibusPriceFactory $priceFactory,
        private readonly Config $config,
        private readonly Escaper $escaper
    ) {
    }

    public function get(int $productId, int $websiteId, int $customerGroupId): ?OmnibusPriceInterface
    {
        return $this->getList([$productId], $websiteId, $customerGroupId)[$productId] ?? null;
    }

    public function getList(array $productIds, int $websiteId, int $customerGroupId): array
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if ($productIds === [] || !$this->config->isEnabled($websiteId)) {
            return [];
        }

        $missingIds = array_values(array_filter(
            $productIds,
            fn(int $productId): bool => !array_key_exists(
                $productId,
                $this->cache[$websiteId][$customerGroupId] ?? []
            )
        ));
        if ($missingIds === []) {
            return array_filter(array_intersect_key(
                $this->cache[$websiteId][$customerGroupId],
                array_flip($productIds)
            ));
        }

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('kkkonrad_omnibus_price_index'))
            ->where('product_id IN (?)', $missingIds)
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId);

        foreach ($missingIds as $missingId) {
            $this->cache[$websiteId][$customerGroupId][$missingId] = null;
        }
        foreach ($connection->fetchAll($select) as $row) {
            $productId = (int)$row['product_id'];
            $reference = $this->config->getDisplayMode() === DisplayMode::ALL
                ? $row['lowest_price']
                : $row['reference_price'];
            $percentage = $reference !== null && (float)$reference > 0
                ? (((float)$reference - (float)$row['current_price']) / (float)$reference) * 100
                : 0.0;
            $message = $reference !== null ? strtr($this->escaper->escapeHtml(
                $this->config->getLabel(),
                ['span', 'i', 'u', 'b']
            ), [
                '{days}' => (string)$this->config->getPeriodDays($websiteId),
                '{omnibus_price}' => number_format((float)$reference, 2) . ' ' . (string)$row['currency_code'],
                '{percentage}' => ($percentage > 0 ? '-' : ($percentage < 0 ? '+' : ''))
                    . number_format(abs($percentage), 0) . '%',
            ]) : '';
            $this->cache[$websiteId][$customerGroupId][$productId] = $this->priceFactory->create(['data' => [
                OmnibusPriceInterface::CURRENT_PRICE => (float)$row['current_price'],
                OmnibusPriceInterface::REFERENCE_PRICE => $row['reference_price'] !== null
                    ? (float)$row['reference_price']
                    : null,
                OmnibusPriceInterface::LOWEST_PRICE => $row['lowest_price'] !== null
                    ? (float)$row['lowest_price']
                    : null,
                OmnibusPriceInterface::CURRENCY_CODE => (string)$row['currency_code'],
                OmnibusPriceInterface::PERIOD_DAYS => $this->config->getPeriodDays($websiteId),
                OmnibusPriceInterface::PROMOTION_STARTED_AT => $row['promotion_started_at'],
                OmnibusPriceInterface::HAS_ACTIVE_DISCOUNT => (bool)$row['has_active_discount'],
                OmnibusPriceInterface::MESSAGE => $message,
            ]]);
        }
        return array_filter(array_intersect_key(
            $this->cache[$websiteId][$customerGroupId],
            array_flip($productIds)
        ));
    }
}
