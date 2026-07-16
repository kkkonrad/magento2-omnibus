<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Magento\Framework\App\ResourceConnection;

class HistoryRebuilder
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly PriceProcessor $processor
    ) {
    }

    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $connection->delete($this->resource->getTableName('kkkonrad_omnibus_price_history'));
        $connection->delete($this->resource->getTableName('kkkonrad_omnibus_price_index'));
        $this->processor->execute(null, 'initial_snapshot');
    }
}
