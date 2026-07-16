<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;

class ProductRepositoryPlugin
{
    public function __construct(
        private readonly OmnibusPriceProviderInterface $provider,
        private readonly StoreManagerInterface $storeManager,
        private readonly Context $httpContext
    ) {
    }

    public function afterGet(ProductRepositoryInterface $subject, ProductInterface $product): ProductInterface
    {
        $this->attach([$product]);
        return $product;
    }

    public function afterGetById(ProductRepositoryInterface $subject, ProductInterface $product): ProductInterface
    {
        $this->attach([$product]);
        return $product;
    }

    public function afterGetList(
        ProductRepositoryInterface $subject,
        ProductSearchResultsInterface $searchResults
    ): ProductSearchResultsInterface {
        $this->attach($searchResults->getItems());
        return $searchResults;
    }

    /** @param ProductInterface[] $products */
    private function attach(array $products): void
    {
        $ids = array_map(static fn(ProductInterface $product): int => (int)$product->getId(), $products);
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $groupId = (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        $prices = $this->provider->getList($ids, $websiteId, $groupId);
        foreach ($products as $product) {
            $extensionAttributes = $product->getExtensionAttributes();
            if ($extensionAttributes && isset($prices[(int)$product->getId()])) {
                $extensionAttributes->setOmnibusPrice($prices[(int)$product->getId()]);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }
    }
}
