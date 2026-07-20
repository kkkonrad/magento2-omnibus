<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;

class HistoryRebuilder
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly PriceProcessor $processor,
        private readonly LockManagerInterface $lockManager
    ) {
    }

    public function execute(): void
    {
        if (!$this->lockManager->lock(PriceProcessor::LOCK_NAME, 5)) {
            throw new LocalizedException(__('Another Omnibus price operation is already running.'));
        }

        $connection = $this->resource->getConnection();
        $transactionStarted = false;
        try {
            $connection->beginTransaction();
            $transactionStarted = true;
            $connection->delete($this->resource->getTableName('kkkonrad_omnibus_price_history'));
            $connection->delete($this->resource->getTableName('kkkonrad_omnibus_price_index'));
            $failed = $this->processor->execute(null, 'initial_snapshot', false);
            if ($failed > 0) {
                throw new LocalizedException(
                    __('Unable to rebuild Omnibus history: %1 price context(s) failed.', $failed)
                );
            }
            $connection->commit();
            $transactionStarted = false;
        } catch (\Throwable $exception) {
            if ($transactionStarted) {
                $connection->rollBack();
            }
            throw $exception;
        } finally {
            $this->lockManager->unlock(PriceProcessor::LOCK_NAME);
        }
    }
}
