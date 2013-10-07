<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Prophecy\Exception\Prediction\AggregateException;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use PSS\SymfonyMockerContainer\Exception\ExpectationException;
use Symfony\Component\DependencyInjection\Container;

class ProphecyContainer extends AbstractMockerContainer
{
    /**
     * @var Prophet
     */
    private $prophet;

    /**
     * @param string  $id              Service Id
     * @param integer $invalidBehavior
     *
     * @return object
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (array_key_exists($id, self::$mockedServices)) {
            return self::$mockedServices[$id]->reveal();
        }

        return parent::get($id, $invalidBehavior);
    }

    /**
     * @param string $classOrInterface Class or Interface name
     *
     * @return ObjectProphecy
     */
    protected function mockService($classOrInterface)
    {
        if (is_null($this->prophet)) {
            $this->prophet = new Prophet;
        }

        return $this->prophet->prophesize($classOrInterface);
    }

    /**
     * Checks mocked object predictions
     */
    public function verifyServiceExpectationsById($serviceId)
    {
        try {
            self::$mockedServices[$serviceId]->checkProphecyMethodsPredictions();
        } catch (AggregateException $exception) {
            throw new ExpectationException($exception->getMessage(), 0, $exception);
        }
    }
}