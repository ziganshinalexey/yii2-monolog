<?php

namespace Mero\Monolog\Handler;

use Mero\Monolog\Exception\HandlerNotFoundException;
use Mero\Monolog\Exception\ParameterNotFoundException;
use Mero\Monolog\Handler\Factory\AbstractFactory;
use Monolog\Logger;
use yii\base\BaseObject;

class Strategy extends BaseObject
{
    /**
     * @var array Handler factory collection
     */
    protected $factories = [];

    /**
     * Method set handler factory collection.
     *
     * @param array $value new value.
     *
     * @return static
     */
    public function setFactories(array $value): self
    {
        $this->factories = $value;

        return $this;
    }

    /**
     * Verifies that the factory class exists.
     *
     * @param string $type Name of type
     *
     * @return bool
     *
     * @throws HandlerNotFoundException When handler factory not found
     * @throws \BadMethodCallException  When handler not implemented
     */
    protected function hasFactory($type)
    {
        if (! array_key_exists($type, $this->factories)) {
            throw new HandlerNotFoundException(
                sprintf("Type '%s' not found in handler factory", $type)
            );
        }
        $factoryClass = &$this->factories[$type];
        if (! class_exists($factoryClass)) {
            throw new \BadMethodCallException(
                sprintf("Type '%s' not implemented", $type)
            );
        }

        return true;
    }

    /**
     * Create a factory object.
     *
     * @param array $config Configuration parameters
     *
     * @return AbstractFactory Factory object
     *
     * @throws ParameterNotFoundException When required parameter not found
     */
    public function createFactory(array $config)
    {
        if (! array_key_exists('type', $config)) {
            throw new ParameterNotFoundException(
                sprintf("Parameter '%s' not found in handler configuration", 'type')
            );
        }
        $this->hasFactory($config['type']);
        if (isset($config['level'])) {
            $config['level'] = Logger::toMonologLevel($config['level']);
        }

        $factoryClass = &$this->factories[$config['type']];

        return new $factoryClass($config);
    }
}
