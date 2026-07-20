<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Console\Command;

use Kkkonrad\Omnibus\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiagnoseCommand extends Command
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('omnibus:diagnose')
            ->setDescription((string)__('Report Omnibus configuration and index completeness'));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->resource->getConnection();
        $source = $this->resource->getTableName('catalog_product_index_price');
        $index = $this->resource->getTableName('kkkonrad_omnibus_price_index');
        $history = $this->resource->getTableName('kkkonrad_omnibus_price_history');
        $sourceCount = (int)$connection->fetchOne($connection->select()->from($source, ['COUNT(*)']));
        $indexCount = (int)$connection->fetchOne($connection->select()->from($index, ['COUNT(*)']));
        $historyCount = (int)$connection->fetchOne($connection->select()->from($history, ['COUNT(*)']));
        $enabledWebsiteIds = [];
        $invalidRetentionWebsites = 0;
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteId = (int)$website->getId();
            if ($this->config->isEnabled($websiteId)) {
                $enabledWebsiteIds[] = $websiteId;
            }
            if ($this->config->getConfiguredRetentionDays() < $this->config->getPeriodDays($websiteId)) {
                ++$invalidRetentionWebsites;
            }
        }

        $missing = 0;
        $extra = 0;
        $missingOpenHistory = 0;
        $duplicateOpenHistory = 0;
        $priceMismatches = 0;
        if ($enabledWebsiteIds !== []) {
            $contextJoin = 'source.entity_id = omnibus.product_id'
                . ' AND source.website_id = omnibus.website_id'
                . ' AND source.customer_group_id = omnibus.customer_group_id';
            $missing = (int)$connection->fetchOne(
                $connection->select()
                    ->from(['source' => $source], [])
                    ->joinLeft(['omnibus' => $index], $contextJoin, [])
                    ->columns(['COUNT(*)'])
                    ->where('source.website_id IN (?)', $enabledWebsiteIds)
                    ->where('omnibus.product_id IS NULL')
            );
            $extra = (int)$connection->fetchOne(
                $connection->select()
                    ->from(['omnibus' => $index], [])
                    ->joinLeft(['source' => $source], $contextJoin, [])
                    ->columns(['COUNT(*)'])
                    ->where('omnibus.website_id IN (?)', $enabledWebsiteIds)
                    ->where('source.entity_id IS NULL')
            );
            $openHistoryJoin = 'omnibus.product_id = open_history.product_id'
                . ' AND omnibus.website_id = open_history.website_id'
                . ' AND omnibus.customer_group_id = open_history.customer_group_id'
                . ' AND open_history.valid_to IS NULL';
            $missingOpenHistory = (int)$connection->fetchOne(
                $connection->select()
                    ->from(['omnibus' => $index], [])
                    ->joinLeft(['open_history' => $history], $openHistoryJoin, [])
                    ->columns(['COUNT(*)'])
                    ->where('omnibus.website_id IN (?)', $enabledWebsiteIds)
                    ->where('open_history.history_id IS NULL')
            );
            $duplicateSelect = $connection->select()
                ->from($history, ['product_id', 'website_id', 'customer_group_id'])
                ->where('website_id IN (?)', $enabledWebsiteIds)
                ->where('valid_to IS NULL')
                ->group(['product_id', 'website_id', 'customer_group_id'])
                ->having('COUNT(*) > 1');
            $duplicateOpenHistory = (int)$connection->fetchOne(
                $connection->select()->from(['duplicates' => $duplicateSelect], ['COUNT(*)'])
            );
            $priceMismatches = (int)$connection->fetchOne(
                $connection->select()
                    ->from(['omnibus' => $index], [])
                    ->joinInner(['open_history' => $history], $openHistoryJoin, [])
                    ->columns(['COUNT(*)'])
                    ->where('omnibus.website_id IN (?)', $enabledWebsiteIds)
                    ->where(
                        'ABS(omnibus.current_price - open_history.effective_price) >= 0.00005'
                        . ' OR ABS(omnibus.regular_price - open_history.regular_price) >= 0.00005'
                    )
            );
        }

        $output->writeln((string)__('Enabled websites: %1', count($enabledWebsiteIds)));
        $output->writeln((string)__('Calculation period: %1 days', $this->config->getPeriodDays()));
        $output->writeln((string)__('Retention: %1 days', $this->config->getRetentionDays()));
        $output->writeln((string)__('Magento price index rows: %1', $sourceCount));
        $output->writeln((string)__('Omnibus index rows: %1', $indexCount));
        $output->writeln((string)__('Omnibus history rows: %1', $historyCount));
        $output->writeln((string)__('Potentially missing contexts: %1', $missing));
        $output->writeln((string)__('Unexpected Omnibus contexts: %1', $extra));
        $output->writeln((string)__('Contexts without open history: %1', $missingOpenHistory));
        $output->writeln((string)__('Duplicate open history contexts: %1', $duplicateOpenHistory));
        $output->writeln((string)__('Current price mismatches: %1', $priceMismatches));
        $output->writeln((string)__('Websites with invalid retention: %1', $invalidRetentionWebsites));

        $errors = $missing + $extra + $missingOpenHistory + $duplicateOpenHistory
            + $priceMismatches + $invalidRetentionWebsites;
        return $errors === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
