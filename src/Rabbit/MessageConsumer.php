<?php

namespace App\Rabbit;

use App\Service\MessageService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class MessageConsumer implements ConsumerInterface
{
    /**
     * @var MessageService MessageService
     */
    private MessageService $messageService;

    /**
     * @param MessageService $messageService
     */
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * On dev, run these commands:
     *  - docker exec -ti symfony_dockerized-php-1 sh   (Get inside the PHP container)
     *  - php bin/console rabbitmq:consumer messaging   (Run execute function)
     *
     * @param AMQPMessage $msg
     *
     * @return string
     */
    public function execute(AMQPMessage $msg)
    {
        #Get time and memory usage Initial
        $start_time = $this->messageService->getTimeSpent();
        $usedMemoryInit = $this->messageService->getUsedMemory();

        $routingKey = $msg->getRoutingKey();
        $message = json_decode($msg->body, true);

        if ($routingKey) {
            #send message to routingKey if it exists
        }

        #Get time and memory usage Ending
        $usedMemoryEnd = $this->messageService->getUsedMemory();
        $end_time = $this->messageService->getTimeSpent();

        echo nl2br("Message sent to ".str_pad($message['receiver'], 35)." --  Memory Used: ".($usedMemoryEnd - $usedMemoryInit)." KB -- TimeSpent: ".($end_time - $start_time)."\n");
    }
}