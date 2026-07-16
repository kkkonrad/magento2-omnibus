<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\ResourceModel\HistoryRecord\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _initSelect(): self
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['product' => $this->getTable('catalog_product_entity')],
                'main_table.product_id = product.entity_id',
                ['sku']
            )
            ->joinLeft(
                ['website' => $this->getTable('store_website')],
                'main_table.website_id = website.website_id',
                ['website_name' => 'name']
            );
        $this->addFilterToMap('sku', 'product.sku');
        $this->addFilterToMap('website_name', 'website.name');
        return $this;
    }
}
