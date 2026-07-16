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

    public function isAutoCleaningEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PREFIX . 'auto_clean');
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

    public function shouldDisplayChildPrices(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PREFIX . 'display_child_prices',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /** @return int[] */
    public function getHiddenCustomerGroupIds(?int $storeId = null): array
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PREFIX . 'hidden_customer_groups',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($value === '') {
            return [];
        }
        return array_values(array_unique(array_map('intval', explode(',', $value))));
    }

    public function shouldDisplayBackendHistory(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PREFIX . 'display_backend_history');
    }

    public function getPercentageMode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PREFIX . 'percentage_mode',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
