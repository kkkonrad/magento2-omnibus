<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Model\ResourceModel\PriceHistory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class PriceProcessor
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly StoreManagerInterface $storeManager,
        private readonly PriceHistory $priceHistory,
        private readonly LoggerInterface $logger
    ) {
    }

    /** @param int[]|null $productIds */
    public function execute(?array $productIds = null, string $source = 'indexer'): void
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTableName('catalog_product_index_price'),
            ['entity_id', 'customer_group_id', 'website_id', 'regular_price' => 'price', 'effective_price' => 'final_price']
        );
        if ($productIds !== null) {
            $productIds = array_values(array_unique(array_map('intval', $productIds)));
            if ($productIds === []) {
                return;
            }
            $select->where('entity_id IN (?)', $productIds);
        }

        foreach ($connection->fetchAll($select) as $row) {
            try {
                $websiteId = (int)$row['website_id'];
                $currencyCode = $this->storeManager->getWebsite($websiteId)->getBaseCurrencyCode();
                $this->priceHistory->record(
                    (int)$row['entity_id'],
                    $websiteId,
                    (int)$row['customer_group_id'],
                    $currencyCode,
                    (float)$row['regular_price'],
                    (float)$row['effective_price'],
                    $source
                );
            } catch (\Throwable $exception) {
                $this->logger->error('Unable to record Omnibus price.', [
                    'row' => $row,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
