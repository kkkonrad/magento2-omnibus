<?php
declare(strict_types=1);

namespace Kkkonrad\Omnibus\Console\Command;

use Kkkonrad\Omnibus\Model\HistoryCleaner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    public function __construct(private readonly HistoryCleaner $cleaner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('omnibus:history:cleanup')
            ->setDescription('Remove expired closed Omnibus price history records');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deleted = $this->cleaner->execute();
        $output->writeln(sprintf('<info>Removed %d expired history record(s).</info>', $deleted));
        return Command::SUCCESS;
    }
}
