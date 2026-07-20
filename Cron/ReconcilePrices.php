<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Cron;

use Kkkonrad\Omnibus\Model\PriceProcessor;
use Magento\Framework\Exception\LocalizedException;

class ReconcilePrices
{
    public function __construct(private readonly PriceProcessor $priceProcessor)
    {
    }

    public function execute(): void
    {
        $failed = $this->priceProcessor->execute(null, 'cron_reconciliation');
        if ($failed > 0) {
            throw new LocalizedException(
                __('Unable to reconcile %1 Omnibus price context(s).', $failed)
            );
        }
    }
}
