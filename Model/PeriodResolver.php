<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Api\PeriodResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PeriodResolver implements PeriodResolverInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function getPeriodFrom(string $periodToUtc, int $days, int $websiteId): string
    {
        $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $timezone = (string)$this->scopeConfig->getValue(
            'general/locale/timezone',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $utc = new \DateTimeZone('UTC');
        $local = (new \DateTimeImmutable($periodToUtc, $utc))->setTimezone(
            new \DateTimeZone($timezone !== '' ? $timezone : 'UTC')
        );
        return $local->modify(sprintf('-%d days', max(1, $days)))
            ->setTimezone($utc)
            ->format('Y-m-d H:i:s');
    }
}
