<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model\ResourceModel;

use Kkkonrad\Omnibus\Api\LowestPriceCalculatorInterface;
use Kkkonrad\Omnibus\Api\PeriodResolverInterface;
use Kkkonrad\Omnibus\Api\PromotionDetectorInterface;
use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\ResourceModel\PriceHistory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;

class PriceHistoryTest extends TestCase
{
    public function testUnchangedPriceKeepsCurrentPriceInRollingLowest(): void
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('forUpdate')->willReturnSelf();

        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchRow')->willReturn([
            'regular_price' => '100.0000',
            'current_price' => '60.0000',
        ]);
        $connection->expects(self::once())
            ->method('update')
            ->with(
                'index_table',
                self::callback(static fn(array $data): bool => $data['lowest_price'] === 60.0),
                self::anything()
            );

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnMap([
            ['kkkonrad_omnibus_price_index', 'index_table'],
            ['kkkonrad_omnibus_price_history', 'history_table'],
        ]);

        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('getPeriodDays')->willReturn(30);

        $calculator = $this->createMock(LowestPriceCalculatorInterface::class);
        $calculator->method('calculate')->willReturn(70.0);
        $periodResolver = $this->createMock(PeriodResolverInterface::class);
        $periodResolver->method('getPeriodFrom')->willReturn('2026-06-21 00:00:00');

        $history = new PriceHistory(
            $resource,
            $config,
            $calculator,
            $this->createMock(PromotionDetectorInterface::class),
            $periodResolver
        );

        $history->record(2044, 1, 0, 'PLN', 100.0, 60.0);
    }
}
