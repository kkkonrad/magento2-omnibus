<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model;

use Kkkonrad\Omnibus\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testProductPageDisplayUsesIndependentStoreFlag(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::once())->method('isSetFlag')->with(
            'kkkonrad_omnibus/general/display_on_product',
            ScopeInterface::SCOPE_STORE,
            2
        )->willReturn(true);

        self::assertTrue((new Config($scopeConfig))->shouldDisplayOnProduct(2));
    }

    public function testListingDisplayUsesIndependentStoreFlag(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::once())->method('isSetFlag')->with(
            'kkkonrad_omnibus/general/display_on_listing',
            ScopeInterface::SCOPE_STORE,
            3
        )->willReturn(false);

        self::assertFalse((new Config($scopeConfig))->shouldDisplayOnListing(3));
    }
}
