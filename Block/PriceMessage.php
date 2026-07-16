<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Block;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;
use Kkkonrad\Omnibus\Api\OmnibusPriceProviderInterface;
use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\Config\Source\DisplayMode;
use Kkkonrad\Omnibus\Model\Config\Source\PercentageMode;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;

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
        private readonly Configurable $configurableType,
        private readonly CatalogHelper $catalogHelper,
        private readonly TaxConfig $taxConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setProduct(ProductInterface $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function getPriceData(): ?OmnibusPriceInterface
    {
        if (!$this->product || (bool)$this->product->getCustomAttribute('hide_omnibus_price')?->getValue()) {
            return null;
        }
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $groupId = (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        if (in_array($groupId, $this->config->getHiddenCustomerGroupIds(), true)) {
            return null;
        }
        $price = $this->priceProvider->get((int)$this->product->getId(), $websiteId, $groupId);
        if (!$price || !$this->shouldShowPrice($price)) {
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
        return $this->formatMessage($price);
    }

    /** @return array<int, string> */
    public function getVariantMessages(): array
    {
        if (!$this->config->shouldDisplayChildPrices()
            || !$this->product
            || $this->product->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }
        $children = $this->configurableType->getUsedProducts($this->product);
        if ($children === []) {
            return [];
        }
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $groupId = (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        $ids = array_map(static fn(ProductInterface $product): int => (int)$product->getId(), $children);
        $prices = $this->priceProvider->getList($ids, $websiteId, $groupId);
        $messages = [];
        foreach ($children as $child) {
            $price = $prices[(int)$child->getId()] ?? null;
            if (!(bool)$child->getCustomAttribute('hide_omnibus_price')?->getValue()
                && $price
                && $this->shouldShowPrice($price)) {
                $messages[(int)$child->getId()] = $this->formatMessage($price);
            }
        }
        return $messages;
    }

    private function formatMessage(OmnibusPriceInterface $price): string
    {
        $reference = (float)$this->getDisplayReference($price);
        $current = $price->getCurrentPrice();
        if ($this->product) {
            $displayType = $this->taxConfig->getPriceDisplayType($this->storeManager->getStore());
            $includingTax = $displayType !== TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
            $reference = (float)$this->catalogHelper->getTaxPrice(
                $this->product,
                $reference,
                $includingTax
            );
            $current = (float)$this->catalogHelper->getTaxPrice(
                $this->product,
                $current,
                $includingTax
            );
        }
        $percentage = $reference > 0
            ? (($reference - $current) / $reference) * 100
            : 0.0;
        $percentageText = $this->formatPercentage($percentage);
        $safeTemplate = $this->escaper->escapeHtml($this->config->getLabel(), ['span', 'i', 'u', 'b']);
        return strtr($safeTemplate, [
            '{days}' => (string)$price->getPeriodDays(),
            '{omnibus_price}' => $this->escaper->escapeHtml(
                $this->priceCurrency->convertAndFormat($reference, false)
            ),
            '{percentage}' => $percentageText,
        ]);
    }

    private function shouldShowPrice(OmnibusPriceInterface $price): bool
    {
        $reference = $this->getDisplayReference($price);
        if ($reference === null) {
            return false;
        }
        if ($this->config->getDisplayMode() === DisplayMode::DISCOUNTED && !$price->hasActiveDiscount()) {
            return false;
        }
        return !$this->config->shouldHideEqual()
            || abs($price->getCurrentPrice() - $reference) >= 0.00005;
    }

    private function getDisplayReference(OmnibusPriceInterface $price): ?float
    {
        return $this->config->getDisplayMode() === DisplayMode::ALL
            ? $price->getLowestPrice()
            : $price->getReferencePrice();
    }

    private function formatPercentage(float $percentage): string
    {
        $mode = $this->config->getPercentageMode();
        if ($mode === PercentageMode::HIDDEN
            || abs($percentage) < 0.00005
            || ($mode === PercentageMode::DISCOUNT_ONLY && $percentage < 0)
            || ($mode === PercentageMode::INCREASE_ONLY && $percentage > 0)) {
            return '';
        }
        return ($percentage > 0 ? '-' : '+') . number_format(abs($percentage), 0) . '%';
    }
}
