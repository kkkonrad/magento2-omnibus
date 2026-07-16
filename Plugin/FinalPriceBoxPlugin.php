<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Plugin;

use Kkkonrad\Omnibus\Block\PriceMessage;
use Kkkonrad\Omnibus\Model\Config;
use Kkkonrad\Omnibus\Model\Config\Source\DisplayPlace;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;

class FinalPriceBoxPlugin
{
    public function __construct(
        private readonly LayoutInterface $layout,
        private readonly Config $config,
        private readonly RequestInterface $request
    ) {
    }

    public function afterToHtml(FinalPriceBox $subject, string $html): string
    {
        $displayPlace = $this->config->getDisplayPlace();
        if ($displayPlace === DisplayPlace::NONE) {
            return $html;
        }
        if ($displayPlace === DisplayPlace::PRODUCT && $this->request->getFullActionName() !== 'catalog_product_view') {
            return $html;
        }
        $product = $subject->getSaleableItem();
        if (!$product || !$product->getId()) {
            return $html;
        }
        /** @var PriceMessage $block */
        $block = $this->layout->createBlock(PriceMessage::class);
        $message = $block->setTemplate('Kkkonrad_Omnibus::price/message.phtml')
            ->setProduct($product)
            ->toHtml();
        return $html . $message;
    }
}
