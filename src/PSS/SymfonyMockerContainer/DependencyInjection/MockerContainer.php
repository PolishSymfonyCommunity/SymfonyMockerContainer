<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Mockery\Mock;
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
     * @return \Mockery\Mock
     */
    public function mock()
    {
        $arguments = func_get_args();
        $id = array_shift($arguments);

        $mock = call_user_func_array(array('Mockery', 'mock'), $arguments);

        return $this->mockService($id, $mock);
    }

    /**
     * Replaces a service with the provided mock object
     *
     * @param  string $id   The id of the service to mock
     * @param  Mock   $mock The mock object to inject
     * @return Mock         The mocked service
     */
    public function setMock($id, $mock)
    {
        return $this->mockService($id, $mock);
    }

    /**
     * @param  string $id
     * @param  mixed  $mock
     * @return Mock
     * @throws \InvalidArgumentException
     */
    protected function mockService($id, $mock)
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Cannot mock a non-existent service: "%s"', $id));
        }

        if (!array_key_exists($id, self::$mockedServices)) {
            self::$mockedServices[$id] = $mock;
        }

        return self::$mockedServices[$id];
    }

    /**
     * @param  string $id
     * @return null
     */
    public function unmock($id)
    {
        unset(self::$mockedServices[$id]);
    }

    /**
     * @param string  $id
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
     * @param string $id
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
