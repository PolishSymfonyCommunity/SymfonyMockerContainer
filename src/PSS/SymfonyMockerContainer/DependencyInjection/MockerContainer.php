<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\Container;

class MockerContainer extends Container
{
    /**
     * @var array $mockedServices
     */
    static private $mockedServices = array();

    /**
     * Takes an id of the service as the first argument.
     * Any other arguments are passed to the Mockery factory.
     *
     * @param string $id        Service ID
     * @param string $className Class name
     *
     * @return object
     * @throws \InvalidArgumentException
     */
    public function mock($id, $className)
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Cannot mock unexisting service: "%s"', $id));
        }

        if (!array_key_exists($id, self::$mockedServices)) {

            $prophet = new Prophet;
            $service = $prophet->prophesize($className)->reveal();

            self::$mockedServices[$id] = $service;
        }

        return self::$mockedServices[$id];
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
     * @return array
     */
    public function getMockedServices()
    {
        return self::$mockedServices;
    }
}
