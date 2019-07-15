<?php

declare(strict_types = 1);

namespace Mero\Monolog\Handler\Factory;

use Monolog\Handler\AmqpHandler;
use Monolog\Logger;
use Userstory\Yii2Amqp\traits\WithAmqpConnectionTrait;

/**
 * Class AmqpFactory.
 */
class AmqpFactory extends AbstractFactory
{
    use WithAmqpConnectionTrait;

    /**
     * {@inheritdoc}
     */
    protected function checkParameters(): void
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function createHandler()
    {
        $channel      = $this->config['exchange'] ?? $this->getAmqpConnection()->channel();
        $exchangeName = $this->config['exchangeName'] ?? 'logs';
        $level        = $this->config['level'] ?? Logger::DEBUG;
        $bubble       = $this->config['bubble'] ?? true;

        return new AmqpHandler($channel, $exchangeName, $level, $bubble);
    }
}
