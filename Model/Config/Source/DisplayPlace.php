<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DisplayPlace implements OptionSourceInterface
{
    public const NONE = 'none';
    public const PRODUCT = 'product';
    public const PRODUCT_CATEGORY = 'product_category';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::PRODUCT, 'label' => __('Product Pages')],
            ['value' => self::PRODUCT_CATEGORY, 'label' => __('Product and Category Pages')],
        ];
    }
}
