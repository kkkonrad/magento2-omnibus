<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Api;

interface PeriodResolverInterface
{
    public function getPeriodFrom(string $periodToUtc, int $days, int $websiteId): string;
}
