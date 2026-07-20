<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model;

use Kkkonrad\Omnibus\Model\HistoryRebuilder;
use Kkkonrad\Omnibus\Model\PriceProcessor;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;
use PHPUnit\Framework\TestCase;

class HistoryRebuilderTest extends TestCase
{
    public function testFailedContextsRollBackEntireRebuild(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects(self::once())->method('beginTransaction')->willReturnSelf();
        $connection->expects(self::exactly(2))->method('delete')->willReturn(1);
        $connection->expects(self::never())->method('commit');
        $connection->expects(self::once())->method('rollBack')->willReturnSelf();

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnArgument(0);

        $processor = $this->createMock(PriceProcessor::class);
        $processor->expects(self::once())
            ->method('execute')
            ->with(null, 'initial_snapshot', false)
            ->willReturn(1);

        $lockManager = $this->createMock(LockManagerInterface::class);
        $lockManager->expects(self::once())
            ->method('lock')
            ->with(PriceProcessor::LOCK_NAME, 5)
            ->willReturn(true);
        $lockManager->expects(self::once())
            ->method('unlock')
            ->with(PriceProcessor::LOCK_NAME)
            ->willReturn(true);

        $rebuilder = new HistoryRebuilder($resource, $processor, $lockManager);

        $this->expectException(LocalizedException::class);
        $rebuilder->execute();
    }
}
