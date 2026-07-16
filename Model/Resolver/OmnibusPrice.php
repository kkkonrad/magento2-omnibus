<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Model\Resolver;

use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

class OmnibusPrice implements ResolverInterface
{
    public function __construct(
        private readonly OmnibusPriceProviderInterface $provider,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): ?array {
        if (!isset($value['model']) || !$value['model'] instanceof ProductInterface) {
            return null;
        }
        $groupId = (int)($context->getExtensionAttributes()?->getCustomerGroupId() ?? 0);
        $price = $this->provider->get(
            (int)$value['model']->getId(),
            (int)$this->storeManager->getStore()->getWebsiteId(),
            $groupId
        );
        if (!$price) {
            return null;
        }
        return [
            'current_price' => $price->getCurrentPrice(),
            'reference_price' => $price->getReferencePrice(),
            'currency_code' => $price->getCurrencyCode(),
            'period_days' => $price->getPeriodDays(),
            'promotion_started_at' => $price->getPromotionStartedAt(),
            'has_active_discount' => $price->hasActiveDiscount(),
        ];
    }
}
