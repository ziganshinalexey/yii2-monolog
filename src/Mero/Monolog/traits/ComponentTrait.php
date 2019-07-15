<?php

declare(strict_types = 1);

namespace Mero\Monolog\traits;

use Mero\Monolog\MonologComponent;
use yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Trait with access an component for the Monolog library.
 */
trait ComponentTrait
{
    /**
     * Свойство содержит экземпляр объекта компонента.
     *
     * @var MonologComponent|null
     */
    protected $monologComponent;

    /**
     * Метод возвращает объект компонеента.
     *
     * @throws InvalidConfigException Если компонент не зарегистрирован.
     *
     * @return MonologComponent|null
     */
    protected function getMonologComponent(): ?Component
    {
        if (! $this->monologComponent) {
            $this->monologComponent = Yii::$app->get('monolog');
        }

        return $this->monologComponent;
    }

    /**
     * Метод задает значение компонента.
     *
     * @param Component $value Объект класса преобразователя.
     *
     * @return static
     */
    public function setMonologComponent(Component $value)
    {
        $this->monologComponent = $value;

        return $this;
    }
}
