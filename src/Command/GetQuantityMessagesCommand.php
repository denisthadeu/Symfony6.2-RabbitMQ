<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Controller\MessageController;

/**
 * Command line to be executed:
 * For first queue: php bin/console fiducial:get-quantity-messages
 * For second queue:php bin/console fiducial:get-quantity-messages test-second-queue
 */
#[AsCommand(name: 'fiducial:get-quantity-messages')]
class GetQuantityMessagesCommand extends Command
{

    public function __construct(
        private MessageController $messageController,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('queue', InputArgument::OPTIONAL, 'Queue Name on RabbitMQ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '============',
            'Messages Quantity Reader',
            '',
        ]);

        $queue = (string) $input->getArgument('queue');

        $readMessage = $this->messageController->quantityMessages($queue);

        $output->writeln('Output: ' . $readMessage);
        $output->writeln('============');

        return Command::SUCCESS;
    }
}