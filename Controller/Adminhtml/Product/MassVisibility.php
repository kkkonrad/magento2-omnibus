<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;

class MassVisibility extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Kkkonrad_Omnibus::product_override';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly ProductAction $productAction
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $value = (int)(bool)$this->getRequest()->getParam('value', 1);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ids = array_map('intval', $collection->getAllIds());
        if ($ids !== []) {
            $this->productAction->updateAttributes($ids, ['hide_omnibus_price' => $value], 0);
        }
        $this->messageManager->addSuccessMessage(
            __('Updated Omnibus price visibility for %1 product(s).', count($ids))
        );
        return $this->resultRedirectFactory->create()->setPath('catalog/product/index');
    }
}
