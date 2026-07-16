<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Console\Command;

use Kkkonrad\Omnibus\Model\HistoryRebuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildCommand extends Command
{
    public function __construct(private readonly HistoryRebuilder $rebuilder)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('omnibus:rebuild')
            ->setDescription((string)__('Delete Omnibus history and create a new initial price snapshot'))
            ->addOption('force', 'f', InputOption::VALUE_NONE, (string)__('Confirm destructive history reset'));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('<error>' . __('This command deletes all Omnibus history. Use --force to continue.')
                . '</error>');
            return Command::FAILURE;
        }
        $this->rebuilder->execute();
        $output->writeln('<info>' . __('Omnibus history rebuilt from the current Magento price index.') . '</info>');
        return Command::SUCCESS;
    }
}
