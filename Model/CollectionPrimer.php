<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model;

use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;

class CollectionPrimer
{
    public function __construct(
        private readonly OmnibusPriceProviderInterface $provider,
        private readonly StoreManagerInterface $storeManager,
        private readonly Context $httpContext
    ) {
    }

    public function execute(Collection $collection): void
    {
        $productIds = [];
        foreach ($collection->getItems() as $product) {
            $productIds[] = (int)$product->getId();
        }
        if ($productIds === []) {
            return;
        }
        $this->provider->getList(
            $productIds,
            (int)$this->storeManager->getStore()->getWebsiteId(),
            (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
        );
    }
}
