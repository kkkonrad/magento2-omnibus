<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model;

use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\HistoryCleaner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

class HistoryCleanerTest extends TestCase
{
    public function testCleanupUsesRetentionForEachWebsite(): void
    {
        $firstWebsite = $this->createConfiguredMock(Website::class, ['getId' => 1]);
        $secondWebsite = $this->createConfiguredMock(Website::class, ['getId' => 2]);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getWebsites')->willReturn([$firstWebsite, $secondWebsite]);

        $config = $this->createMock(Config::class);
        $config->expects(self::exactly(2))
            ->method('getRetentionDays')
            ->willReturnMap([[1, 30], [2, 90]]);

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects(self::exactly(2))
            ->method('delete')
            ->with(
                'history_table',
                self::callback(static fn(array $where): bool => isset($where['website_id = ?'])
                    && in_array($where['website_id = ?'], [1, 2], true)
                    && in_array('valid_to IS NOT NULL', $where, true)
                    && isset($where['valid_to < ?']))
            )
            ->willReturnOnConsecutiveCalls(2, 3);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturn('history_table');

        $cleaner = new HistoryCleaner($resource, $config, $storeManager);

        self::assertSame(5, $cleaner->execute());
    }
}
