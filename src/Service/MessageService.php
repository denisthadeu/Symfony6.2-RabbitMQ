<?php

namespace App\Service;

use App\Rabbit\MessagingProducer;
//use Faker\Factory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PhpAmqpLib\Message\AMQPMessage;

class MessageService
{
    private $messagingProducer;
    private $connection;
    private $channel;
    private $params;

    CONST PASSIVE = false;
    CONST DURABLE = true;
    CONST EXCLUSIVE = false;
    CONST AUTO_DELETE = false;

    /**
     * @param MessagingProducer $messagingProducer
     * @param ParameterBagInterface $params
     */
    public function __construct(MessagingProducer $messagingProducer, ParameterBagInterface $params)
    {
        $this->messagingProducer = $messagingProducer;
        $this->params = $params->all()['RabbitMQ'];
        $this->connection = null;
        $this->channel = null;
    }

    /**
     * Create a message on RabbitMQ
     *
     * @param String $message Json Message
     *
     * @return void
     */
    public function createMessage(String $message): void
    {
        $this->messagingProducer->publish($message);
    }

    /**
     * Prepare Fake message on RabbitMQ
     *
     * @param int $numberOfUsers
     *
     * @return void
     */
    public function prepareFakeMessage(int $numberOfUsers): void
    {
        for ($i=0; $i<$numberOfUsers; $i++) {
            $message = json_encode($this->prepareMessage(
                'emailSender@fake.com',
                'emailReceiver@fake.com',
                'Welcome to the Rabbit MQ Tester. This test is number: '. $i));
            $this->publishMessage($message, $this->params['application_rountingKey']);
            #$this->createMessage($message);
        }
    }

    /**
     * Prepare message for rabbitMQ
     *
     * @param string $sender
     * @param string $receiver
     * @param string $body
     *
     * @return array
     */
    public function prepareMessage(string $sender, string $receiver, string $body): array
    {
        return [
            'sender' => $sender,
            'receiver' => $receiver,
            'message' => $body
        ];
    }

    /**
     * @param $bool
     *
     * @return float
     */
    public function getUsedMemory($bool = false): float
    {
        return round(memory_get_usage($bool) / 1024);
    }

    public function getPeakMemory($bool = false): float
    {
        return round(memory_get_peak_usage($bool) / 1024);
    }

    /**
     * @return mixed
     */
    public function getTimeSpent()
    {
        return microtime(true);
    }

    /**
     * Start Connection to rabbit mq
     *
     * @return void
     */
    public function startConnectRabbitMQService():void
    {
        if (!$this->connection) {
            try {
                $this->connection = new AMQPStreamConnection($this->params['application_env'], $this->params['application_port'], $this->params['application_user'], $this->params['application_secret']);
                $this->channel = $this->connection->channel();
            } catch (\Exception $e) {
                die(' Catch error: '. $e);
            }
        }
    }

    /**
     * Close connection to rabbitMQ
     *
     * @return void
     */
    public function closeConnectRabbitMQService():void
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Get last message waiting on the queue
     *
     * @param $queue
     * @param $ack
     *
     * @return mixed
     */
    public function retrieveValues($queue, $ack = 0)
    {
        $message = $this->channel->basic_get($queue);
        if ($message && $ack) {
            $message->ack();
        }

        return $message;
    }

    /**
     * Get queue Info
     *
     * @param $queue
     *
     * @return mixed
     */
    public function getQueueInfo($queue)
    {
        return $this->channel->queue_declare($queue, self::PASSIVE, self::DURABLE, self::EXCLUSIVE, self::AUTO_DELETE);
    }

    /**
     * publish a message on Queue
     *
     * @param string $message
     * @param string $routingKey
     *
     * @return void
     */
    public function publishMessage(string $message, string $routingKey = ''): void
    {
        $AMQPMessage = new AMQPMessage(
            $message,
            ['delivery_mode' => 2] # make message persistent, so it is not lost if server crashes or quits
        );
        $this->channel->basic_publish(
            $AMQPMessage,
            $this->params['application_exchange'],
            $routingKey
        );
    }

    public function getRabbitParam(): array
    {
        return $this->params;
    }
}