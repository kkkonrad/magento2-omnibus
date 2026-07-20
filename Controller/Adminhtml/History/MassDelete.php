<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Controller\Adminhtml\History;

use Kkkonrad\Omnibus\Model\ResourceModel\HistoryRecord\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
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
        try {
            $selected = $this->getRequest()->getParam('selected');
            $excluded = $this->getRequest()->getParam('excluded');

            if ($selected !== null && !is_array($selected)) {
                throw new LocalizedException(__('Please select valid price history records.'));
            }
            if ($excluded !== null && $excluded !== 'false' && !is_array($excluded)) {
                throw new LocalizedException(__('Please select valid price history records.'));
            }
            if (is_array($excluded) && array_filter(
                $excluded,
                static fn($id): bool => !is_scalar($id) || !ctype_digit((string)$id) || (int)$id <= 0
            )) {
                throw new LocalizedException(__('Please select valid price history records.'));
            }

            if (is_array($selected) && array_filter(
                $selected,
                static fn($id): bool => !is_scalar($id) || !ctype_digit((string)$id) || (int)$id <= 0
            )) {
                throw new LocalizedException(__('Please select valid price history records.'));
            }

            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $selectedCount = $collection->getSize();
            $collection->addFieldToFilter('valid_to', ['notnull' => true]);

            $deleted = 0;
            foreach ($collection as $record) {
                $record->delete();
                ++$deleted;
            }
            $this->messageManager->addSuccessMessage(__('Deleted %1 price history record(s).', $deleted));
            $protected = max(0, $selectedCount - $deleted);
            if ($protected > 0) {
                $this->messageManager->addWarningMessage(
                    __('Skipped %1 active price history record(s).', $protected)
                );
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
