<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Api\LowestPriceCalculatorInterface;
use Magento\Framework\App\ResourceConnection;

class LowestPriceCalculator implements LowestPriceCalculatorInterface
{
    public function __construct(private readonly ResourceConnection $resource)
    {
    }

    public function calculate(
        int $productId,
        int $websiteId,
        int $customerGroupId,
        string $periodFrom,
        string $periodTo
    ): ?float {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                $this->resource->getTableName('kkkonrad_omnibus_price_history'),
                ['lowest' => 'MIN(effective_price)']
            )
            ->where('product_id = ?', $productId)
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('valid_from < ?', $periodTo)
            ->where('valid_to IS NULL OR valid_to > ?', $periodFrom);
        $value = $connection->fetchOne($select);
        return $value === false || $value === null ? null : (float)$value;
    }
}
