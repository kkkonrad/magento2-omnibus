<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Controller\Adminhtml\History;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Kkkonrad_Omnibus::history_view';

    public function __construct(Context $context, private readonly PageFactory $pageFactory)
    {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Kkkonrad_Omnibus::history');
        $page->getConfig()->getTitle()->prepend(__('Omnibus Price History'));
        return $page;
    }
}
