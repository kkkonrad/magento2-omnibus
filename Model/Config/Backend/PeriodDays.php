<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class PeriodDays extends Value
{
    private const RETENTION_PATH = 'kkkonrad_omnibus/general/retention_days';

    public function beforeSave(): self
    {
        $periodDays = (int)$this->getValue();
        if ($periodDays < 30) {
            throw new LocalizedException(__('The Omnibus calculation period must be at least 30 days.'));
        }

        $fieldsetRetention = $this->getFieldsetDataValue('retention_days');
        $retentionDays = $fieldsetRetention !== null
            ? (int)$fieldsetRetention
            : (int)$this->_config->getValue(self::RETENTION_PATH);
        if ($retentionDays > 0 && $periodDays > $retentionDays) {
            throw new LocalizedException(
                __('The Omnibus calculation period must not exceed history retention (%1 days).', $retentionDays)
            );
        }

        return parent::beforeSave();
    }
}
