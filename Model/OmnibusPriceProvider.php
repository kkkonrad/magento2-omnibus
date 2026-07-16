<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;
use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Kkkonrad\Omnibus\Model\Data\OmnibusPriceFactory;
use Magento\Framework\App\ResourceConnection;

class OmnibusPriceProvider implements OmnibusPriceProviderInterface
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly OmnibusPriceFactory $priceFactory,
        private readonly Config $config
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

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('kkkonrad_omnibus_price_index'))
            ->where('product_id IN (?)', $productIds)
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId);

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $productId = (int)$row['product_id'];
            $result[$productId] = $this->priceFactory->create(['data' => [
                OmnibusPriceInterface::CURRENT_PRICE => (float)$row['current_price'],
                OmnibusPriceInterface::REFERENCE_PRICE => $row['reference_price'] !== null
                    ? (float)$row['reference_price']
                    : null,
                OmnibusPriceInterface::CURRENCY_CODE => (string)$row['currency_code'],
                OmnibusPriceInterface::PERIOD_DAYS => $this->config->getPeriodDays($websiteId),
                OmnibusPriceInterface::PROMOTION_STARTED_AT => $row['promotion_started_at'],
                OmnibusPriceInterface::HAS_ACTIVE_DISCOUNT => (bool)$row['has_active_discount'],
            ]]);
        }
        return $result;
    }
}
