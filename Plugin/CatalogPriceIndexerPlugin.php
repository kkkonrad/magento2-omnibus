<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Model\PriceProcessor;
use Magento\Catalog\Model\Indexer\Product\Price;

class CatalogPriceIndexerPlugin
{
    public function __construct(private readonly PriceProcessor $priceProcessor)
    {
    }

    public function afterExecuteFull(Price $subject, mixed $result): mixed
    {
        $this->priceProcessor->execute(null, 'full_indexer');
        return $result;
    }

    public function afterExecute(Price $subject, mixed $result, array $ids): mixed
    {
        $this->priceProcessor->execute($ids);
        return $result;
    }

    public function afterExecuteList(Price $subject, mixed $result, array $ids): mixed
    {
        $this->priceProcessor->execute($ids);
        return $result;
    }

    public function afterExecuteRow(Price $subject, mixed $result, int $id): mixed
    {
        $this->priceProcessor->execute([$id]);
        return $result;
    }
}
