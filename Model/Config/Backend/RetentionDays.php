<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class RetentionDays extends Value
{
    private const PERIOD_PATH = 'kkkonrad_omnibus/general/period_days';

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly StoreManagerInterface $storeManager,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

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

        foreach ($this->storeManager->getWebsites() as $website) {
            $websitePeriodDays = (int)$this->_config->getValue(
                self::PERIOD_PATH,
                ScopeInterface::SCOPE_WEBSITE,
                (int)$website->getId()
            );
            if ($retentionDays < $websitePeriodDays) {
                throw new LocalizedException(
                    __(
                        'Price history retention must not be shorter than the calculation period for website %1 (%2 days).',
                        $website->getName(),
                        $websitePeriodDays
                    )
                );
            }
        }

        return parent::beforeSave();
    }
}
