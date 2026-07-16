<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\ResourceModel\HistoryRecord;

use Kkkonrad\Omnibus\Model\HistoryRecord;
use Kkkonrad\Omnibus\Model\ResourceModel\HistoryRecord as HistoryRecordResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(HistoryRecord::class, HistoryRecordResource::class);
    }
}
