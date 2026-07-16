<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;

class ProductCollectionPlugin
{
    public function __construct(
        private readonly OmnibusPriceProviderInterface $provider,
        private readonly StoreManagerInterface $storeManager,
        private readonly Context $httpContext
    ) {
    }

    public function afterLoad(
        Collection $subject,
        Collection $result,
        bool $printQuery = false,
        bool $logQuery = false
    ): Collection {
        $productIds = [];
        foreach ($result->getItems() as $product) {
            $productIds[] = (int)$product->getId();
        }
        if ($productIds !== []) {
            $this->provider->getList(
                $productIds,
                (int)$this->storeManager->getStore()->getWebsiteId(),
                (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
            );
        }
        return $result;
    }
}
