<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Controller\Adminhtml\History;

use Kkkonrad\Omnibus\Model\ResourceModel\HistoryRecord\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'Kkkonrad_Omnibus::history_delete';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection as $record) {
            $record->delete();
            ++$deleted;
        }
        $this->messageManager->addSuccessMessage(__('Deleted %1 price history record(s).', $deleted));
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
