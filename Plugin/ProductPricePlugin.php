<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Block\PriceMessage;
use Kkkonrad\Omnibus\Model\Config;
use Magento\Catalog\Block\Product\Price;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;

class ProductPricePlugin
{
    public function __construct(
        private readonly LayoutInterface $layout,
        private readonly Config $config,
        private readonly RequestInterface $request,
        private readonly Registry $registry
    ) {
    }

    public function afterToHtml(Price $subject, string $html): string
    {
        if ($this->request->getFullActionName() !== 'catalog_product_view'
            || !$this->config->shouldDisplayOnProduct()) {
            return $html;
        }
        $product = $subject->getProduct();
        $currentProduct = $this->registry->registry('current_product');
        if (!$product
            || !$product->getId()
            || !$currentProduct
            || (int)$currentProduct->getId() !== (int)$product->getId()
            || $product->getTypeId() === Configurable::TYPE_CODE) {
            return $html;
        }
        /** @var PriceMessage $block */
        $block = $this->layout->createBlock(PriceMessage::class);
        return $html . $block->setTemplate('Kkkonrad_Omnibus::price/message.phtml')
            ->setProduct($product)
            ->toHtml();
    }
}
