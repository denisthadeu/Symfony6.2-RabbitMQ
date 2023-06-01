<?php

namespace App\Controller;

use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
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
     * Create messagens on Rabbit MQ
     *
     * @Route("/send-message/{numberOfMessages}", name="send_message", methods={"GET"})
     *
     * @param int $numberOfMessages
     *
     * @return JsonResponse
     */
    public function createMessages(int $numberOfMessages): JsonResponse
    {
        #Get time and memory usage Initial
        $start_time = $this->messageService->getTimeSpent();
        $usedMemoryInit = $this->messageService->getUsedMemory();

        #Send message to RabbitMQ
        $this->messageService->startConnectRabbitMQService();
        $this->messageService->prepareFakeMessage($numberOfMessages);
        $this->messageService->closeConnectRabbitMQService();

        #Get time and memory usage Ending
        $usedMemoryEnd = $this->messageService->getPeakMemory(true);
        $end_time = $this->messageService->getTimeSpent();

        return new JsonResponse([
            'status' => true,
            'NumberOfMessagesCreatedOnRabbitMQ' => $numberOfMessages,
            'memoryUsed' => ($usedMemoryEnd - $usedMemoryInit).'KB',
            'timeSpent' => ($end_time - $start_time).' seconds'
        ]);
    }

    /**
     * Get messages from RabbitMQ queue
     *
     * @Route("/get-message/{queue}/{ack}", name="get_message", methods={"GET"})
     *
     * @param String $queue Queue Name on RabbitMQ
     * @param int    $ack   1 = Remove from queue
     *
     * @return JsonResponse
     */
    public function receiveMessages(string $queue = '', int $ack = 0)
    {
        #Get time and memory usage Initial
        $start_time = $this->messageService->getTimeSpent();
        $usedMemoryInit = $this->messageService->getUsedMemory();

        if (!$queue) {
            $queue = $this->messageService->getRabbitParam()['application_queue'];
        }

        #Receive messages from RabbitMQ
        $this->messageService->startConnectRabbitMQService();

        $rabbitMqQuantity = $this->messageService->getQueueInfo($queue);
        if($rabbitMqQuantity && $rabbitMqQuantity[1]) {
            for ($i = 0; $i < $rabbitMqQuantity[1]; $i++) {
                #Get messages form Rabbit MQ
                $rabbitMqValues = $this->messageService->retrieveValues($queue, $ack);
                if ($rabbitMqValues) {
                    $routingKey = $rabbitMqValues->getRoutingKey();
                    $messages[] = json_decode($rabbitMqValues->body);

                    #send to second routing queue
                    if ($routingKey && $ack) {
                        $this->messageService->publishMessage($rabbitMqValues->body);
                    }
                }
            }
        }

        $this->messageService->closeConnectRabbitMQService();

        #Get time and memory usage Ending
        $usedMemoryEnd = $this->messageService->getPeakMemory(true);
        $end_time = $this->messageService->getTimeSpent();

        return new JsonResponse([
            'status' => true,
            'MessagesReceivedOnRabbitMQ' => $messages ?? [],
            'memoryUsed' => ($usedMemoryEnd - $usedMemoryInit).'KB',
            'timeSpent' => ($end_time - $start_time).' seconds'
        ]);
    }

    /**
     * Get the quantity of messages waiting on RabbitMQ
     *
     * @Route("/get-quantity-message/{queue}", name="get_quantity_message", methods={"GET"})
     *
     * @param String $queue Queue Name on RabbitMQ
     *
     * @return JsonResponse
     */
    public function quantityMessages(string $queue = '')
    {
        #Get time and memory usage Initial
        $start_time = $this->messageService->getTimeSpent();
        $usedMemoryInit = $this->messageService->getUsedMemory();

        #Receive messages from RabbitMQ

        if (!$queue) {
            $queue = $this->messageService->getRabbitParam()['application_queue'];
        }

        $this->messageService->startConnectRabbitMQService();
        $rabbitMqValues = $this->messageService->getQueueInfo($queue);
        $this->messageService->closeConnectRabbitMQService();

        if($rabbitMqValues && $rabbitMqValues[1]) {
            $numberOfMessages = $rabbitMqValues[1];
        }

        #Get time and memory usage Ending
        $usedMemoryEnd = $this->messageService->getPeakMemory(true);
        $end_time = $this->messageService->getTimeSpent();

        return new JsonResponse([
            'status' => true,
            'NumberOfMessagesReceivedOnRabbitMQ' => $numberOfMessages ?? 0,
            'memoryUsed' => ($usedMemoryEnd - $usedMemoryInit).'KB',
            'timeSpent' => ($end_time - $start_time).' seconds'
        ]);
    }
}