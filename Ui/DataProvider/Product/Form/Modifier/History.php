<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Ui\DataProvider\Product\Form\Modifier;

use Kkkonrad\Omnibus\Model\Config;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Escaper;

class History extends AbstractModifier
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResourceConnection $resource,
        private readonly Escaper $escaper,
        private readonly Config $config
    ) {
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        $productId = (int)$this->request->getParam('id');
        if (!$this->config->shouldDisplayBackendHistory() || $productId <= 0) {
            return $meta;
        }
        $meta['omnibus_history'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Omnibus Price History'),
                        'componentType' => 'fieldset',
                        'collapsible' => true,
                        'opened' => false,
                        'sortOrder' => 95,
                    ],
                ],
            ],
            'children' => [
                'omnibus_history_content' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/html',
                                'content' => $this->renderHistory($productId),
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return $meta;
    }

    private function renderHistory(int $productId): string
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('kkkonrad_omnibus_price_history'))
            ->where('product_id = ?', $productId)
            ->order('valid_from DESC')
            ->limit(20);
        $rows = $connection->fetchAll($select);
        if ($rows === []) {
            return '<p>' . $this->escaper->escapeHtml(__('No price history is available yet.')) . '</p>';
        }
        $html = '<div class="admin__data-grid-wrap"><table class="data-grid">'
            . '<thead><tr><th>Website</th><th>Group</th><th>Regular</th><th>Effective</th>'
            . '<th>Currency</th><th>Valid from</th><th>Valid to</th><th>Source</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach (['website_id', 'customer_group_id', 'regular_price', 'effective_price',
                         'currency_code', 'valid_from', 'valid_to', 'change_source'] as $column) {
                $html .= '<td>' . $this->escaper->escapeHtml((string)($row[$column] ?? '—')) . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</tbody></table></div>';
    }
}
