<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\Container;

class MockerContainer extends Container
{
    /**
     * @var array $mockedServices
     */
    static private $mockedServices = array();

    /**
     * @var Prophet
     */
    private $prophet;

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

            $this->lazyInitializeProphet();
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
     * Initialises Prophet object
     */
    private function lazyInitializeProphet()
    {
        if (is_null($this->prophet)) {
            $this->prophet = new Prophet;
        }
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
            return self::$mockedServices[$id]->reveal();
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

    /**
     * @param string $id Service Id
     */
    public function unmock($id)
    {
        unset(self::$mockedServices[$id]);
    }
}