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
            ->setDescription('Report Omnibus configuration and index completeness');
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

        $output->writeln('Enabled: ' . ($this->config->isEnabled() ? 'yes' : 'no'));
        $output->writeln('Calculation period: ' . $this->config->getPeriodDays() . ' days');
        $output->writeln('Retention: ' . $this->config->getRetentionDays() . ' days');
        $output->writeln('Magento price index rows: ' . $sourceCount);
        $output->writeln('Omnibus index rows: ' . $indexCount);
        $output->writeln('Omnibus history rows: ' . $historyCount);
        $output->writeln('Potentially missing contexts: ' . $missing);
        return $missing === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
