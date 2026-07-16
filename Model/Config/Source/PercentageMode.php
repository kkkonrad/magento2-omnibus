<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PercentageMode implements OptionSourceInterface
{
    public const DISCOUNT_ONLY = 'discount_only';
    public const INCREASE_ONLY = 'increase_only';
    public const ALL_EXCEPT_ZERO = 'all_except_zero';
    public const HIDDEN = 'hidden';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::DISCOUNT_ONLY, 'label' => __('Discounts Only')],
            ['value' => self::INCREASE_ONLY, 'label' => __('Price Increases Only')],
            ['value' => self::ALL_EXCEPT_ZERO, 'label' => __('All Except Zero')],
            ['value' => self::HIDDEN, 'label' => __('Hidden')],
        ];
    }
}
