<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DisplayMode implements OptionSourceInterface
{
    public const DISCOUNTED = 'discounted';
    public const ALL = 'all';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::DISCOUNTED, 'label' => __('Discounted Products')],
            ['value' => self::ALL, 'label' => __('All Products')],
        ];
    }
}
