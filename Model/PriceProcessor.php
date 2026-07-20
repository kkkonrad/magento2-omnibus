<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Model\ResourceModel\PriceHistory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class PriceProcessor
{
    public const LOCK_NAME = 'kkkonrad_omnibus_price_processing';

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly StoreManagerInterface $storeManager,
        private readonly PriceHistory $priceHistory,
        private readonly LoggerInterface $logger,
        private readonly LockManagerInterface $lockManager
    ) {
    }

    /** @param int[]|null $productIds */
    public function execute(
        ?array $productIds = null,
        string $source = 'indexer',
        bool $acquireLock = true
    ): int {
        if ($acquireLock && !$this->lockManager->lock(self::LOCK_NAME, 30)) {
            throw new LocalizedException(__('Another Omnibus price operation is already running.'));
        }

        try {
            return $this->process($productIds, $source);
        } finally {
            if ($acquireLock) {
                $this->lockManager->unlock(self::LOCK_NAME);
            }
        }
    }

    /** @param int[]|null $productIds */
    private function process(?array $productIds, string $source): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTableName('catalog_product_index_price'),
            ['entity_id', 'customer_group_id', 'website_id', 'regular_price' => 'price', 'effective_price' => 'final_price']
        );
        if ($productIds !== null) {
            $productIds = array_values(array_unique(array_map('intval', $productIds)));
            if ($productIds === []) {
                return 0;
            }
            $select->where('entity_id IN (?)', $productIds);
        }

        $failed = 0;
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
                ++$failed;
                $this->logger->error('Unable to record Omnibus price.', [
                    'row' => $row,
                    'exception' => $exception,
                ]);
            }
        }

        return $failed;
    }
}
