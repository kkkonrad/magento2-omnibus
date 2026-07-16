<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Ui\Component\Listing\Column;

use Magento\Backend\Model\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class HistoryActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item['product_id'])) {
                $item[$name]['view'] = [
                    'href' => $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $item['product_id']]),
                    'label' => __('View Product'),
                ];
            }
        }
        return $dataSource;
    }
}
