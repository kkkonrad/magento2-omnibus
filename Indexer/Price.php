<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Indexer;

use Kkkonrad\Omnibus\Model\PriceProcessor;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Price implements ActionInterface, MviewActionInterface
{
    public function __construct(private readonly PriceProcessor $priceProcessor)
    {
    }

    public function execute($ids): void
    {
        $this->priceProcessor->execute(array_map('intval', (array)$ids), 'mview');
    }

    public function executeFull(): void
    {
        $this->priceProcessor->execute(null, 'omnibus_full_indexer');
    }

    public function executeList(array $ids): void
    {
        $this->priceProcessor->execute($ids, 'omnibus_indexer');
    }

    public function executeRow($id): void
    {
        $this->priceProcessor->execute([(int)$id], 'omnibus_indexer');
    }
}
