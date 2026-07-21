<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Test\Unit\Model\Data;

use Kkkonrad\Omnibus\Api\Data\OmnibusPriceInterface;
use Kkkonrad\Omnibus\Model\Data\OmnibusPrice;
use PHPUnit\Framework\TestCase;

class OmnibusPriceTest extends TestCase
{
    public function testServiceContractGetterReturnsActiveDiscountState(): void
    {
        $price = new OmnibusPrice([OmnibusPriceInterface::HAS_ACTIVE_DISCOUNT => true]);

        self::assertTrue($price->hasActiveDiscount());
        self::assertTrue($price->getHasActiveDiscount());
    }
}
