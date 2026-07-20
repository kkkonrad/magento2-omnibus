<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class HistoryCleaner
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function execute(): int
    {
        $connection = $this->resource->getConnection();
        $historyTable = $this->resource->getTableName('kkkonrad_omnibus_price_history');
        $deleted = 0;

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteId = (int)$website->getId();
            $before = gmdate(
                'Y-m-d H:i:s',
                time() - ($this->config->getRetentionDays($websiteId) * 86400)
            );
            $deleted += $connection->delete($historyTable, [
                'website_id = ?' => $websiteId,
                'valid_to IS NOT NULL',
                'valid_to < ?' => $before,
            ]);
        }

        return $deleted;
    }
}
