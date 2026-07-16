<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Api\Data;

interface OmnibusPriceInterface
{
    public const CURRENT_PRICE = 'current_price';
    public const REFERENCE_PRICE = 'reference_price';
    public const CURRENCY_CODE = 'currency_code';
    public const PERIOD_DAYS = 'period_days';
    public const PROMOTION_STARTED_AT = 'promotion_started_at';
    public const HAS_ACTIVE_DISCOUNT = 'has_active_discount';

    public function getCurrentPrice(): float;

    public function getReferencePrice(): ?float;

    public function getCurrencyCode(): string;

    public function getPeriodDays(): int;

    public function getPromotionStartedAt(): ?string;

    public function hasActiveDiscount(): bool;
}
