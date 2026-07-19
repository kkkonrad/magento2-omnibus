<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Setup\Patch\Data;

use Kkkonrad\Omnibus\Model\Config\Source\DisplayPlace;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SplitDisplayPlaceConfig implements DataPatchInterface
{
    private const OLD_PATH = 'kkkonrad_omnibus/general/display_place';
    private const PRODUCT_PATH = 'kkkonrad_omnibus/general/display_on_product';
    private const LISTING_PATH = 'kkkonrad_omnibus/general/display_on_listing';

    public function __construct(private readonly ResourceConnection $resourceConnection)
    {
    }

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $configTable = $this->resourceConnection->getTableName('core_config_data');
        $rows = $connection->fetchAll(
            $connection->select()->from($configTable)->where('path = ?', self::OLD_PATH)
        );

        foreach ($rows as $row) {
            $displayOnProduct = in_array(
                $row['value'],
                [DisplayPlace::PRODUCT, DisplayPlace::PRODUCT_CATEGORY],
                true
            );
            $displayOnListing = $row['value'] === DisplayPlace::PRODUCT_CATEGORY;
            foreach ([
                self::PRODUCT_PATH => (int)$displayOnProduct,
                self::LISTING_PATH => (int)$displayOnListing,
            ] as $path => $value) {
                $connection->insertOnDuplicate($configTable, [
                    'scope' => $row['scope'],
                    'scope_id' => $row['scope_id'],
                    'path' => $path,
                    'value' => $value,
                ], ['value']);
            }
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
