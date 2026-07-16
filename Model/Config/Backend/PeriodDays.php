<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class PeriodDays extends Value
{
    public function beforeSave(): self
    {
        if ((int)$this->getValue() < 30) {
            throw new LocalizedException(__('The Omnibus calculation period must be at least 30 days.'));
        }
        return parent::beforeSave();
    }
}
