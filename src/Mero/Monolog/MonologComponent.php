<?php

declare(strict_types = 1);

namespace Mero\Monolog;

use InvalidArgumentException;
use Mero\Monolog\Exception\HandlerNotFoundException;
use Mero\Monolog\Exception\LoggerNotFoundException;
use Mero\Monolog\Exception\ParameterNotFoundException;
use Mero\Monolog\Handler\Strategy;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * MonologComponent is an component for the Monolog library.
 *
 * @author Rafael Mello <merorafael@gmail.com>
 */
class MonologComponent extends Component
{
    /**
     * Default name for channel.
     *
     * @var string
     */
    protected $defaultChannelName = 'main';

    /**
     * Logger channels.
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Handler strategy to create factory.
     *
     * @var Strategy
     */
    protected $strategy;

    /**
     * Strategy config.
     *
     * @var array
     */
    protected $strategyConfig = ['class' => Strategy::class];

    /**
     * Set default channel name.
     *
     * @param string $value New value.
     *
     * @return MonologComponent
     */
    public function setDefaultChannelName(string $value): self
    {
        $this->defaultChannelName = $value;

        return $this;
    }

    /**
     * Return default channel name.
     *
     * @return string
     *
     * @throws InvalidConfigException If channel name is empty.
     */
    public function getDefaultChannelName(): string
    {
        if (empty($this->defaultChannelName)) {
            throw new InvalidConfigException('Default channel name object can not be null');
        }

        return $this->defaultChannelName;
    }

    /**
     * Method set strategy config.
     *
     * @param array $value New config value.
     *
     * @return static
     */
    public function setStrategyConfig(array $value): self
    {
        $this->strategyConfig = array_merge($this->strategyConfig, $value);

        return $this;
    }

    /**
     * Method set logger channels.
     *
     * @param array $value new channels value.
     *
     * @return static
     */
    public function setChannels(array $value): self
    {
        $this->channels = $value;

        return $this;
    }

    /**
     * Method return strategy config.
     *
     * @return array
     */
    protected function getStrategyConfig(): array
    {
        return $this->strategyConfig;
    }

    /**
     * @throws HandlerNotFoundException
     * @throws ParameterNotFoundException
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->strategy = Yii::createObject($this->getStrategyConfig());

        foreach ($this->channels as $name => $config) {
            $this->createChannel($name, $config);
        }
        parent::init();
    }

    /**
     * Create a logger channel.
     *
     * @param string $name   Logger channel name
     * @param array  $config Logger channel configuration
     *
     * @return void
     *
     * @throws InvalidArgumentException When the channel already exists
     * @throws HandlerNotFoundException  When a handler configuration is invalid
     * @throws ParameterNotFoundException
     */
    public function createChannel($name, array $config): void
    {
        $handlers   = [];
        $processors = [];
        if (! empty($config['handler']) && is_array($config['handler'])) {
            foreach ($config['handler'] as $handler) {
                if (! is_array($handler) && ! $handler instanceof AbstractHandler) {
                    throw new HandlerNotFoundException();
                }
                if (is_array($handler)) {
                    $handlerObject = $this->createHandlerInstance($handler);
                    if (array_key_exists('formatter', $handler) && $handler['formatter'] instanceof FormatterInterface) {
                        $handlerObject->setFormatter($handler['formatter']);
                    }
                } else {
                    $handlerObject = $handler;
                }
                $handlers[] = $handlerObject;
            }
        }
        if (! empty($config['processor']) && is_array($config['processor'])) {
            $processors = $config['processor'];
        }
        $this->openChannel($name, $handlers, $processors);
    }

    /**
     * Open a new logger channel.
     *
     * @param string $name       Logger channel name
     * @param array  $handlers   Handlers collection
     * @param array  $processors Processors collection
     *
     * @return void
     */
    protected function openChannel($name, array $handlers, array $processors): void
    {
        if ($this->hasLogger($name)) {
            throw new InvalidArgumentException(sprintf('Channel \'%s\' already exists', $name));
        }

        $this->channels[$name] = new Logger($name, $handlers, $processors);
    }

    /**
     * Close a open logger channel.
     *
     * @param string $name Logger channel name
     *
     * @return void
     */
    public function closeChannel($name): void
    {
        if (isset($this->channels[$name])) {
            unset($this->channels[$name]);
        }
    }

    /**
     * Create handler instance.
     *
     * @param array $config Configuration parameters
     *
     * @return AbstractHandler
     *
     * @throws ParameterNotFoundException
     */
    protected function createHandlerInstance(array $config): AbstractHandler
    {
        $factory = $this->strategy->createFactory($config);

        return $factory->createHandler();
    }

    /**
     * Checks if the given logger exists.
     *
     * @param string $name Logger name
     *
     * @return bool
     */
    public function hasLogger($name): bool
    {
        return isset($this->channels[$name]) && ($this->channels[$name] instanceof Logger);
    }

    /**
     * Return logger object.
     *
     * @param string $name Logger name
     *
     * @return Logger Logger object
     *
     * @throws LoggerNotFoundException
     * @throws InvalidConfigException
     */
    public function getLogger($name = null): Logger
    {
        if (null === $name) {
            $name = $this->getDefaultChannelName();
        }

        if (! $this->hasLogger($name)) {
            throw new LoggerNotFoundException(sprintf('Logger instance \'%s\' not found', $name));
        }

        return $this->channels[$name];
    }
}
