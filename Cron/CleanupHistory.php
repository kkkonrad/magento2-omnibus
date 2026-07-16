<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Cron;

use Kkkonrad\Omnibus\Model\HistoryCleaner;
use Kkkonrad\Omnibus\Model\Config;

class CleanupHistory
{
    public function __construct(
        private readonly HistoryCleaner $cleaner,
        private readonly Config $config
    ) {
    }

    public function execute(): void
    {
        if ($this->config->isAutoCleaningEnabled()) {
            $this->cleaner->execute();
        }
    }
}
