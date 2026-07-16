<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model;

use Kkkonrad\Omnibus\Model\PeriodResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

class PeriodResolverTest extends TestCase
{
    public function testCalendarDaysAreCalculatedInStoreTimezoneAcrossDstChange(): void
    {
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $website = $this->createMock(Website::class);
        $website->method('getDefaultStore')->willReturn($store);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getWebsite')->with(1)->willReturn($website);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('Europe/Warsaw');

        $resolver = new PeriodResolver($scopeConfig, $storeManager);

        self::assertSame(
            '2026-02-28 11:00:00',
            $resolver->getPeriodFrom('2026-03-30 10:00:00', 30, 1)
        );
    }
}
