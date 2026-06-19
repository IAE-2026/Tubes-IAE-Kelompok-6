<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Location;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class ConsumeRabbitMQ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume status and payment events from RabbitMQ to update parking spots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = env('RABBITMQ_HOST', 'iae-sso.virtualfri.id');
        $port = env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $password = env('RABBITMQ_PASSWORD', 'guest');
        $vhost = env('RABBITMQ_VHOST', '/');
        $exchangeName = env('RABBITMQ_EXCHANGE', 'iae.central.exchange');
        $queueName = env('RABBITMQ_QUEUE', 'team06_smart_parking_queue');

        $this->info("Connecting to RabbitMQ at $host:$port...");

        try {
            $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
            $channel = $connection->channel();

            // Declare Exchange (topic type)
            $channel->exchange_declare($exchangeName, 'topic', false, true, false);

            // Declare Queue
            $channel->queue_declare($queueName, false, true, false, false);

            // Bind routing keys
            $routingKeys = [
                'parking.slot.occupied',
                'parking.slot.released',
                'parking.payment.completed'
            ];

            foreach ($routingKeys as $bindingKey) {
                $channel->queue_bind($queueName, $exchangeName, $bindingKey);
                $this->info("Bound to exchange '$exchangeName' with routing key '$bindingKey'");
            }

            $this->info("Waiting for messages in queue '$queueName'. To exit press CTRL+C");

            $callback = function (AMQPMessage $msg) {
                $routingKey = $msg->getRoutingKey();
                $body = json_decode($msg->body, true);

                $this->info(" [x] Received event '$routingKey' with body: " . $msg->body);
                Log::info('[RabbitMQ Consume] Message received', ['routing_key' => $routingKey, 'body' => $body]);

                try {
                    // Extract message data (flexible lookup)
                    $data = $body['data'] ?? $body['message']['data'] ?? $body['message'] ?? $body;
                    $locationId = $data['location_id'] ?? null;
                    $slots = (int) ($data['slots'] ?? 1);

                    if (!$locationId) {
                        $this->error("Error: location_id missing from event payload");
                        return;
                    }

                    $location = Location::find($locationId);
                    if (!$location) {
                        $this->error("Error: Location $locationId not found in local DB");
                        return;
                    }

                    if ($routingKey === 'parking.slot.occupied') {
                        if ($location->available_spots >= $slots) {
                            $location->available_spots -= $slots;
                            $location->save();
                            $this->info("Occupied $slots spot(s) for location $locationId. Remaining: $location->available_spots");
                        } else {
                            $this->error("Error: Insufficient spots to occupy at location $locationId");
                        }
                    } elseif ($routingKey === 'parking.slot.released' || $routingKey === 'parking.payment.completed') {
                        if ($location->available_spots + $slots <= $location->total_spots) {
                            $location->available_spots += $slots;
                            $location->save();
                            $this->info("Released $slots spot(s) for location $locationId. Available: $location->available_spots");
                        } else {
                            $this->error("Error: Releasing spots exceeds total spots capacity for location $locationId");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing event message: " . $e->getMessage());
                    Log::error('[RabbitMQ Consume] Callback error', ['error' => $e->getMessage()]);
                }
            };

            $channel->basic_consume($queueName, '', false, true, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            $this->error("RabbitMQ Consumer Error: " . $e->getMessage());
            Log::error('[RabbitMQ Consume] Connection/Runtime error', ['error' => $e->getMessage()]);
        }
    }
}
