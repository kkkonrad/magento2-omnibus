<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\FormKey;

class RebuildButton extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly FormKey $formKeyModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getRebuildUrl(): string
    {
        return $this->getUrl('kkkonrad_omnibus/history/rebuild');
    }

    public function getFormKeyValue(): string
    {
        return $this->formKeyModel->getFormKey();
    }
}
