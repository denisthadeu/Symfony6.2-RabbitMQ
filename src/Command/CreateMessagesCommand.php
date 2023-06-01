<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Controller\MessageController;

// Command line to be executed: php bin/console fiducial:create-messages 10
#[AsCommand(name: 'fiducial:create-messages')]
class CreateMessagesCommand extends Command
{

    public function __construct(
        private MessageController $messageController,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('numberOfMessages', InputArgument::REQUIRED, 'Number of Messages to be created');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            '============',
            'Messages Creator',
            '',
        ]);

        $numberOfMessages = $input->getArgument('numberOfMessages');

        $createMessage = $this->messageController->createMessages($numberOfMessages);

        $output->writeln('Number of messages created: ' . $numberOfMessages);
        $output->writeln('');
        $output->writeln('Output: ' . $createMessage);
        $output->writeln('============');

        return Command::SUCCESS;
    }
}