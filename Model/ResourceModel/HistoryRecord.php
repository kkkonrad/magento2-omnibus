<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class HistoryRecord extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('kkkonrad_omnibus_price_history', 'history_id');
    }
}
