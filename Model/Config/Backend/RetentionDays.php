<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class RetentionDays extends Value
{
    public function beforeSave(): self
    {
        $retentionDays = (int)$this->getValue();
        $fieldsetData = $this->getData('fieldset_data');
        $periodDays = is_array($fieldsetData) && isset($fieldsetData['period_days'])
            ? (int)$fieldsetData['period_days']
            : 30;

        if ($retentionDays < max(30, $periodDays)) {
            throw new LocalizedException(
                __('Price history retention must not be shorter than the calculation period (%1 days).', $periodDays)
            );
        }
        return parent::beforeSave();
    }
}
