<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Magento\Framework\Model\AbstractModel;

class HistoryRecord extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Kkkonrad\Omnibus\Model\ResourceModel\HistoryRecord::class);
    }
}
