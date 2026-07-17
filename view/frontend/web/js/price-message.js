define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $message = $(element),
            $form = $('#product_addtocart_form'),
            initialMessage = String($message.data('initial-message') || ''),
            variantMessages = $message.data('variant-messages') || {};

        if (!$form.length || !Object.keys(variantMessages).length) {
            return;
        }
        function getSelectedProductId() {
            var productId = String($form.find('[name="selected_configurable_option"]').val() || ''),
                swatchRenderer = $('.product-info-main .swatch-opt').first().data('mage-SwatchRenderer');

            if (!productId && swatchRenderer && typeof swatchRenderer.getProductId === 'function') {
                productId = String(swatchRenderer.getProductId() || '');
            }
            return productId;
        }

        function renderMessage() {
            var productId = getSelectedProductId(),
                message = productId && variantMessages[productId]
                    ? variantMessages[productId]
                    : initialMessage;

            $message.find('span').html(message);
            $message.toggle(message !== '');
        }

        function renderAfterMagentoUpdate() {
            window.requestAnimationFrame(renderMessage);
        }

        $('.product-info-main .price-box')
            .on('updatePrice.kkkonradOmnibus', renderAfterMagentoUpdate);
        $(document).on(
            'change.kkkonradOmnibus click.kkkonradOmnibus',
            '#product_addtocart_form [name^="super_attribute"], .product-info-main .swatch-option',
            renderAfterMagentoUpdate
        );
        $('.product-info-main .swatch-opt').each(function () {
            new MutationObserver(renderAfterMagentoUpdate).observe(this, {
                attributes: true,
                subtree: true,
                attributeFilter: ['data-option-selected']
            });
        });
        renderMessage();
    };
});
