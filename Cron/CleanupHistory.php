<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Cron;

use Kkkonrad\Omnibus\Model\HistoryCleaner;

class CleanupHistory
{
    public function __construct(private readonly HistoryCleaner $cleaner)
    {
    }

    public function execute(): void
    {
        $this->cleaner->execute();
    }
}
