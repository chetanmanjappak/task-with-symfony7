<?php
namespace App\Command;

use App\Repository\ScanRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ProcessScanMessage;

/**
 * Command for processing pending scans and dispatching notifications.
 */
#[AsCommand(name: 'app:process-scan')]
class ProcessScanCommand extends Command
{
    private ScanRepository $scanRepository;
    private MessageBusInterface $bus;

    /**
     * Constructor.
     *
     * @param ScanRepository $scanRepository The repository to fetch scans.
     * @param MessageBusInterface $bus The message bus to dispatch messages.
     */
    public function __construct(ScanRepository $scanRepository, MessageBusInterface $bus) {
        $this->scanRepository = $scanRepository;
        $this->bus = $bus;
        parent::__construct();
    }

    /**
     * Configures the command options and description.
     */
    protected function configure()
    {
        $this->setDescription('Processes pending scans and dispatches notifications.');
    }

    /**
     * Executes the command to process pending scans and dispatch messages.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int Returns 0 on success, or another integer on failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('The scanning scheduler has started at '.date('Y-m-d H:i:s'));
        $scanIds = $this->scanRepository->findScanningInProgressScanIds();
        if (!empty($scanIds)) {
            $output->writeln('The process of handling scans is beginning at '.date('Y-m-d H:i:s').'. Total scans to be processed: '.count($scanIds));
            foreach ($scanIds as $scanId) {
                $this->bus->dispatch(new ProcessScanMessage($scanId['ci_upload_id']));
            }
        } else {
            $output->writeln('No scans are currently in progress at '.date('Y-m-d H:i:s'));
        }
        $output->writeln('The scanning scheduler has finished the job at '.date('Y-m-d H:i:s'));
        $output->writeln('');
        return Command::SUCCESS;
    }
}
