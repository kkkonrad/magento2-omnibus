<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Model\CollectionPrimer;
use Kkkonrad\Omnibus\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Block\Product\ProductsList;

class ProductWidgetPlugin
{
    public function __construct(
        private readonly CollectionPrimer $primer,
        private readonly Config $config
    ) {
    }

    public function afterCreateCollection(ProductsList $subject, Collection $collection): Collection
    {
        if ($this->config->shouldDisplayOnListing()) {
            $this->primer->execute($collection);
        }
        return $collection;
    }
}
