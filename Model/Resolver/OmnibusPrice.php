<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Resolver;

use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

class OmnibusPrice implements BatchResolverInterface
{
    public function __construct(private readonly OmnibusPriceProviderInterface $provider)
    {
    }

    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $products = [];
        /** @var BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            $product = $request->getValue()['model'] ?? null;
            if ($product instanceof ProductInterface) {
                $products[(int)$product->getId()] = $product;
            }
        }
        $groupId = (int)($context->getExtensionAttributes()?->getCustomerGroupId() ?? 0);
        $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();
        $prices = $this->provider->getList(
            array_keys($products),
            $websiteId,
            $groupId
        );
        $response = new BatchResponse();
        foreach ($requests as $request) {
            $product = $request->getValue()['model'] ?? null;
            $price = $product instanceof ProductInterface ? ($prices[(int)$product->getId()] ?? null) : null;
            $response->addResponse($request, $price ? [
                'current_price' => $price->getCurrentPrice(),
                'reference_price' => $price->getReferencePrice(),
                'lowest_price' => $price->getLowestPrice(),
                'currency_code' => $price->getCurrencyCode(),
                'period_days' => $price->getPeriodDays(),
                'promotion_started_at' => $price->getPromotionStartedAt(),
                'has_active_discount' => $price->hasActiveDiscount(),
                'message' => $price->getMessage(),
            ] : null);
        }
        return $response;
    }
}
