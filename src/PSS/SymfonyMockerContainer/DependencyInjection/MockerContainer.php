<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MockerContainer extends Container
{
    /**
     * @var Prophet
     */
    private $prophet;

    /**
     * @var array $mockedServices
     */
    static private $mockedServices = array();

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        $this->prophet = new Prophet;
    }

    /**
     * Takes an id of the service as the first argument.
     * Any other arguments are passed to the Mockery factory.
     *
     * @param string $id               Service ID
     * @param string $classOrInterface Class name
     *
     * @return ObjectProphecy
     * @throws \InvalidArgumentException
     */
    public function mock($id, $classOrInterface)
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Cannot mock unexisting service: "%s"', $id));
        }

        if (!array_key_exists($id, self::$mockedServices)) {

            $service = $this->prophet->prophesize($classOrInterface);

            self::$mockedServices[$id] = $service;
        }

        return self::$mockedServices[$id];
    }

    /**
     * @param string $id Service Id
     *
     * @return boolean
     */
    public function has($id)
    {
        if (array_key_exists($id, self::$mockedServices)) {
            return true;
        }

        return parent::has($id);
    }

    /**
     * @param string $id Service Id
     */
    public function unmock($id)
    {
        unset(self::$mockedServices[$id]);
    }

    /**
     * @param string  $id              Service Id
     * @param integer $invalidBehavior
     *
     * @return object
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (array_key_exists($id, self::$mockedServices)) {
            return self::$mockedServices[$id];
        }

        return parent::get($id, $invalidBehavior);
    }

    /**
     * @return array
     */
    public function getMockedServices()
    {
        return self::$mockedServices;
    }

    /**
     * @param Prophet $prophet
     */
    public function setProphet(Prophet $prophet)
    {
        $this->prophet = $prophet;
    }

    /**
     * Checks Prophet objects predictions
     */
    public function checkPredictions()
    {
        $this->prophet->checkPredictions();
    }

    /**
     * Cleans up services mocks
     */
    public function cleanUpMockedServices()
    {
        foreach (self::$mockedServices as $id => $service) {
            $this->unmock($id);
        }
    }
}
