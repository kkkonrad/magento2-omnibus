<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Model\CollectionPrimer;
use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\Config\Source\DisplayPlace;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ProductListPlugin
{
    public function __construct(
        private readonly CollectionPrimer $primer,
        private readonly Config $config
    ) {
    }

    public function afterGetLoadedProductCollection(ListProduct $subject, Collection $collection): Collection
    {
        if ($this->config->getDisplayPlace() === DisplayPlace::PRODUCT_CATEGORY) {
            $this->primer->execute($collection);
        }
        return $collection;
    }
}
