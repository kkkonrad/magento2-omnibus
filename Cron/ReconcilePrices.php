<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Cron;

use Kkkonrad\Omnibus\Model\PriceProcessor;

class ReconcilePrices
{
    public function __construct(private readonly PriceProcessor $priceProcessor)
    {
    }

    public function execute(): void
    {
        $this->priceProcessor->execute(null, 'cron_reconciliation');
    }
}
