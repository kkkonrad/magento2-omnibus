<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Data;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;
use Magento\Framework\DataObject;

class OmnibusPrice extends DataObject implements OmnibusPriceInterface
{
    public function getCurrentPrice(): float
    {
        return (float)$this->getData(self::CURRENT_PRICE);
    }

    public function getReferencePrice(): ?float
    {
        $value = $this->getData(self::REFERENCE_PRICE);
        return $value === null ? null : (float)$value;
    }

    public function getLowestPrice(): ?float
    {
        $value = $this->getData(self::LOWEST_PRICE);
        return $value === null ? null : (float)$value;
    }

    public function getCurrencyCode(): string
    {
        return (string)$this->getData(self::CURRENCY_CODE);
    }

    public function getPeriodDays(): int
    {
        return (int)$this->getData(self::PERIOD_DAYS);
    }

    public function getPromotionStartedAt(): ?string
    {
        $value = $this->getData(self::PROMOTION_STARTED_AT);
        return $value === null ? null : (string)$value;
    }

    public function hasActiveDiscount(): bool
    {
        return (bool)$this->getData(self::HAS_ACTIVE_DISCOUNT);
    }

    public function getMessage(): string
    {
        return (string)$this->getData(self::MESSAGE);
    }
}
