<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Api\Data;

interface OmnibusPriceInterface
{
    public const CURRENT_PRICE = 'current_price';
    public const REFERENCE_PRICE = 'reference_price';
    public const LOWEST_PRICE = 'lowest_price';
    public const CURRENCY_CODE = 'currency_code';
    public const PERIOD_DAYS = 'period_days';
    public const PROMOTION_STARTED_AT = 'promotion_started_at';
    public const HAS_ACTIVE_DISCOUNT = 'has_active_discount';
    public const MESSAGE = 'message';

    /**
     * Return the current effective price.
     *
     * @return float
     */
    public function getCurrentPrice(): float;

    /**
     * Return the price before an active promotion.
     *
     * @return float|null
     */
    public function getReferencePrice(): ?float;

    /**
     * Return the lowest price in the configured period.
     *
     * @return float|null
     */
    public function getLowestPrice(): ?float;

    /**
     * Return the website currency code.
     *
     * @return string
     */
    public function getCurrencyCode(): string;

    /**
     * Return the calculation period in days.
     *
     * @return int
     */
    public function getPeriodDays(): int;

    /**
     * Return the active promotion start date.
     *
     * @return string|null
     */
    public function getPromotionStartedAt(): ?string;

    /**
     * Check whether a discount is currently active.
     *
     * @return bool
     */
    public function hasActiveDiscount(): bool;

    /**
     * Return whether a discount is currently active for service-contract serialization.
     *
     * @return bool
     */
    public function getHasActiveDiscount(): bool;

    /**
     * Return the formatted storefront message.
     *
     * @return string
     */
    public function getMessage(): string;
}
