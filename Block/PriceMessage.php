<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Block;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;
use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\Config\Source\DisplayMode;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class PriceMessage extends Template
{
    private ?ProductInterface $product = null;

    public function __construct(
        Template\Context $context,
        private readonly OmnibusPriceProviderInterface $priceProvider,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly Context $httpContext,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setProduct(ProductInterface $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getPriceData(): ?OmnibusPriceInterface
    {
        if (!$this->product || (bool)$this->product->getCustomAttribute('hide_omnibus_price')?->getValue()) {
            return null;
        }
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $groupId = (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        $price = $this->priceProvider->get((int)$this->product->getId(), $websiteId, $groupId);
        if (!$price || $price->getReferencePrice() === null) {
            return null;
        }
        if ($this->config->getDisplayMode() === DisplayMode::DISCOUNTED && !$price->hasActiveDiscount()) {
            return null;
        }
        if ($this->config->shouldHideEqual()
            && abs($price->getCurrentPrice() - $price->getReferencePrice()) < 0.00005) {
            return null;
        }
        return $price;
    }

    public function getMessage(): string
    {
        $price = $this->getPriceData();
        if (!$price) {
            return '';
        }
        $reference = (float)$price->getReferencePrice();
        $percentage = $reference > 0
            ? (($reference - $price->getCurrentPrice()) / $reference) * 100
            : 0.0;
        $safeTemplate = $this->escaper->escapeHtml($this->config->getLabel(), ['span', 'i', 'u', 'b']);
        return strtr($safeTemplate, [
            '{days}' => (string)$price->getPeriodDays(),
            '{omnibus_price}' => $this->escaper->escapeHtml(
                $this->priceCurrency->format($reference, false)
            ),
            '{percentage}' => ($percentage > 0 ? '-' : ($percentage < 0 ? '+' : ''))
                . number_format(abs($percentage), 0) . '%',
        ]);
    }
}
