<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\ResourceModel;

use Kkkonrad\Omnibus\Api\LowestPriceCalculatorInterface;
use Kkkonrad\Omnibus\Api\PromotionDetectorInterface;
use Kkkonrad\Omnibus\Api\PeriodResolverInterface;
use Kkkonrad\Omnibus\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class PriceHistory
{
    private const HISTORY_TABLE = 'kkkonrad_omnibus_price_history';
    private const INDEX_TABLE = 'kkkonrad_omnibus_price_index';

    private readonly AdapterInterface $connection;

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly Config $config,
        private readonly LowestPriceCalculatorInterface $lowestPriceCalculator,
        private readonly PromotionDetectorInterface $promotionDetector,
        private readonly PeriodResolverInterface $periodResolver
    ) {
        $this->connection = $resource->getConnection();
    }

    public function record(
        int $productId,
        int $websiteId,
        int $customerGroupId,
        string $currencyCode,
        float $regularPrice,
        float $effectivePrice,
        string $source = 'indexer'
    ): void {
        if (!$this->config->isEnabled($websiteId)) {
            return;
        }

        $indexTable = $this->resource->getTableName(self::INDEX_TABLE);
        $historyTable = $this->resource->getTableName(self::HISTORY_TABLE);
        $now = gmdate('Y-m-d H:i:s');
        $contextWhere = [
            'product_id = ?' => $productId,
            'website_id = ?' => $websiteId,
            'customer_group_id = ?' => $customerGroupId,
        ];

        $this->connection->beginTransaction();
        try {
            $select = $this->connection->select()
                ->from($indexTable)
                ->where('product_id = ?', $productId)
                ->where('website_id = ?', $websiteId)
                ->where('customer_group_id = ?', $customerGroupId)
                ->forUpdate(true);
            $previous = $this->connection->fetchRow($select);

            if ($previous && $this->pricesEqual((float)$previous['regular_price'], $regularPrice)
                && $this->pricesEqual((float)$previous['current_price'], $effectivePrice)) {
                $rollingFrom = $this->periodResolver->getPeriodFrom(
                    $now,
                    $this->config->getPeriodDays($websiteId),
                    $websiteId
                );
                $rollingLowest = $this->lowestPriceCalculator->calculate(
                    $productId,
                    $websiteId,
                    $customerGroupId,
                    $rollingFrom,
                    $now
                );
                $this->connection->update($indexTable, [
                    'lowest_price' => $rollingLowest ?? $effectivePrice,
                    'calculated_at' => $now,
                ], $contextWhere);
                $this->connection->commit();
                return;
            }

            $activeDiscount = $this->promotionDetector->hasActiveDiscount($regularPrice, $effectivePrice);
            $referencePrice = null;
            $promotionStartedAt = null;
            $periodFrom = null;
            $periodTo = null;
            $rollingPeriodTo = $now;
            $rollingPeriodFrom = $this->periodResolver->getPeriodFrom(
                $now,
                $this->config->getPeriodDays($websiteId),
                $websiteId
            );
            $rollingLowestPrice = $effectivePrice;

            if ($previous) {
                $this->connection->update(
                    $historyTable,
                    ['valid_to' => $now],
                    $contextWhere + ['valid_to IS NULL' => null]
                );

                $wasDiscounted = (bool)$previous['has_active_discount'];
                $startsReduction = $this->promotionDetector->startsReduction(
                    $wasDiscounted,
                    (float)$previous['current_price'],
                    $regularPrice,
                    $effectivePrice
                );
                if ($startsReduction) {
                    $periodTo = $now;
                    $periodFrom = $rollingPeriodFrom;
                    $referencePrice = $this->lowestPriceCalculator->calculate(
                        $productId,
                        $websiteId,
                        $customerGroupId,
                        $periodFrom,
                        $periodTo
                    );
                    $promotionStartedAt = $now;
                } elseif ($activeDiscount && $wasDiscounted) {
                    $referencePrice = $previous['reference_price'] !== null
                        ? (float)$previous['reference_price']
                        : null;
                    $promotionStartedAt = $previous['promotion_started_at'];
                    $periodFrom = $previous['period_from'];
                    $periodTo = $previous['period_to'];
                }
                $historicalLowest = $this->lowestPriceCalculator->calculate(
                    $productId,
                    $websiteId,
                    $customerGroupId,
                    $rollingPeriodFrom,
                    $rollingPeriodTo
                );
                if ($historicalLowest !== null) {
                    $rollingLowestPrice = min($rollingLowestPrice, $historicalLowest);
                }
            }

            $this->connection->insert($historyTable, [
                'product_id' => $productId,
                'website_id' => $websiteId,
                'customer_group_id' => $customerGroupId,
                'currency_code' => $currencyCode,
                'regular_price' => $regularPrice,
                'effective_price' => $effectivePrice,
                'valid_from' => $now,
                'valid_to' => null,
                'change_source' => $source,
            ]);

            $this->connection->insertOnDuplicate($indexTable, [
                'product_id' => $productId,
                'website_id' => $websiteId,
                'customer_group_id' => $customerGroupId,
                'currency_code' => $currencyCode,
                'regular_price' => $regularPrice,
                'current_price' => $effectivePrice,
                'reference_price' => $referencePrice,
                'lowest_price' => $rollingLowestPrice,
                'promotion_started_at' => $promotionStartedAt,
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'has_active_discount' => (int)$activeDiscount,
                'calculated_at' => $now,
            ], [
                'currency_code', 'regular_price', 'current_price', 'reference_price', 'lowest_price',
                'promotion_started_at', 'period_from', 'period_to', 'has_active_discount', 'calculated_at',
            ]);
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    private function pricesEqual(float $first, float $second): bool
    {
        return abs($first - $second) < 0.00005;
    }
}
