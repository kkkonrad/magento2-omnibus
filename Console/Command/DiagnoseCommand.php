<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Console\Command;

use Kkkonrad\Omnibus\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiagnoseCommand extends Command
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly Config $config
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
        $missing = max(0, $sourceCount - $indexCount);

        $output->writeln((string)__('Enabled: %1', $this->config->isEnabled() ? __('yes') : __('no')));
        $output->writeln((string)__('Calculation period: %1 days', $this->config->getPeriodDays()));
        $output->writeln((string)__('Retention: %1 days', $this->config->getRetentionDays()));
        $output->writeln((string)__('Magento price index rows: %1', $sourceCount));
        $output->writeln((string)__('Omnibus index rows: %1', $indexCount));
        $output->writeln((string)__('Omnibus history rows: %1', $historyCount));
        $output->writeln((string)__('Potentially missing contexts: %1', $missing));
        return $missing === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
