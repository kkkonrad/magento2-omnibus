<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Console\Command;

use Kkkonrad\Omnibus\Model\PriceProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReconcileCommand extends Command
{
    public function __construct(private readonly PriceProcessor $processor)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('omnibus:reconcile')
            ->setDescription((string)__('Compare the Magento price index with the Omnibus price history'));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processor->execute(null, 'manual_reconciliation');
        $output->writeln('<info>' . __('Omnibus prices reconciled.') . '</info>');
        return Command::SUCCESS;
    }
}
