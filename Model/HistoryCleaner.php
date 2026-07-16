<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Magento\Framework\App\ResourceConnection;

class HistoryCleaner
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly Config $config
    ) {
    }

    public function execute(): int
    {
        $before = gmdate('Y-m-d H:i:s', time() - ($this->config->getRetentionDays() * 86400));
        return $this->resource->getConnection()->delete(
            $this->resource->getTableName('kkkonrad_omnibus_price_history'),
            ['valid_to IS NOT NULL', 'valid_to < ?' => $before]
        );
    }
}
