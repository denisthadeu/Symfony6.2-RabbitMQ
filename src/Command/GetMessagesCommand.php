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
 * For first queue:        php bin/console fiducial:get-messages
 * for second queue:       php bin/console fiducial:get-messages test-second-queue
 * To remove first queue:  php bin/console fiducial:get-messages test-queue 1
 * To remove second queue: php bin/console fiducial:get-messages test-second-queue 1
*/
#[AsCommand(name: 'fiducial:get-messages')]
class GetMessagesCommand extends Command
{

    public function __construct(
        private MessageController $messageController,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('queue', InputArgument::OPTIONAL, 'Queue Name on RabbitMQ');
        $this->addArgument('ack', InputArgument::OPTIONAL, 'If equal to 1, this removes message from the queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '============',
            'Messages Reader',
            '',
        ]);

        $queue = (string) $input->getArgument('queue');
        $ack = (int) $input->getArgument('ack');

        $readMessage = $this->messageController->receiveMessages($queue, $ack);

        if ($ack) {
            $output->writeln('Removed from queue');
            $output->writeln('');
        }

        $output->writeln('Output: ' . $readMessage);
        $output->writeln('============');

        return Command::SUCCESS;
    }
}