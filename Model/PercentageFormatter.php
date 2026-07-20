<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Model\Config\Source\PercentageMode;

class PercentageFormatter
{
    public function __construct(private readonly Config $config)
    {
    }

    public function format(float $percentage, ?int $storeId = null): string
    {
        $mode = $this->config->getPercentageMode($storeId);
        if ($mode === PercentageMode::HIDDEN
            || abs($percentage) < 0.00005
            || ($mode === PercentageMode::DISCOUNT_ONLY && $percentage < 0)
            || ($mode === PercentageMode::INCREASE_ONLY && $percentage > 0)) {
            return '';
        }

        return ($percentage > 0 ? '-' : '+') . number_format(abs($percentage), 0) . '%';
    }
}
