<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PREFIX = 'kkkonrad_omnibus/general/';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function isEnabled(?int $websiteId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PREFIX . 'enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    public function getPeriodDays(?int $websiteId = null): int
    {
        return max(1, (int)$this->scopeConfig->getValue(
            self::XML_PREFIX . 'period_days',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        ));
    }

    public function getRetentionDays(): int
    {
        return max($this->getPeriodDays(), (int)$this->scopeConfig->getValue(self::XML_PREFIX . 'retention_days'));
    }

    public function getDisplayPlace(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PREFIX . 'display_place',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getDisplayMode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PREFIX . 'display_mode',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function shouldHideEqual(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PREFIX . 'hide_equal',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getLabel(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PREFIX . 'label',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
