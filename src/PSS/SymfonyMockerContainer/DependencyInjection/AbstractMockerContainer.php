<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;

abstract class AbstractMockerContainer extends Container implements MockerContainerInterface
{
    /**
     * @var array $mockedServices
     */
    protected static $mockedServices = array();

    /**
     * Takes an id of the service as the first argument.
     * Any other arguments are passed to the Mockery factory.
     *
     * @param string $id               Service ID
     * @param string $classOrInterface Class name
     *
     * @return object
     * @throws \InvalidArgumentException
     */
    public function mock($id, $classOrInterface)
    {
        $arguments = func_get_args();
        $id        = array_shift($arguments);

        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Cannot mock unexisting service: "%s"', $id));
        }

        if (!array_key_exists($id, self::$mockedServices)) {
            self::$mockedServices[$id] = $this->mockService($classOrInterface);
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
     * @param string $classOrInterface Class or Interface name
     *
     * @return mixed
     */
    abstract protected function mockService($classOrInterface);

    /**
     * @return array
     */
    public function getMockedServices()
    {
        return self::$mockedServices;
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

    /**
     * Checks mocked objects predictions
     */
    public function verifyExpectations()
    {
        foreach (array_keys(self::$mockedServices) as $id) {
            $this->verifyServiceExpectationsById($id);
        }
    }

    public function setMock($id, $mock)
    {
        self::$mockedServices[$id] = $mock;
    }
}