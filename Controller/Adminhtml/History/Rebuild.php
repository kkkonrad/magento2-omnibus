<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Controller\Adminhtml\History;

use Kkkonrad\Omnibus\Model\HistoryRebuilder;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Rebuild extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Kkkonrad_Omnibus::rebuild';

    public function __construct(Context $context, private readonly HistoryRebuilder $rebuilder)
    {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        try {
            $this->rebuilder->execute();
            $this->messageManager->addSuccessMessage(__('Omnibus history has been rebuilt.'));
        } catch (\Throwable $exception) {
            $this->messageManager->addExceptionMessage($exception, __('Unable to rebuild Omnibus history.'));
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
