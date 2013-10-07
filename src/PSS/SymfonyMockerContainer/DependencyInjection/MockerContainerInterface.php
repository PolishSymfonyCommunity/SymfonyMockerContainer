<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

interface MockerContainerInterface
{
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
    public function mock($id, $classOrInterface);

    /**
     * @param string $id Service Id
     *
     * @return boolean
     */
    public function has($id);

    /**
     * @param string  $id              Service Id
     * @param integer $invalidBehavior
     *
     * @return object
     */
    public function get($id, $invalidBehavior);

    /**
     * @return array
     */
    public function getMockedServices();

    /**
     * Checks mocked objects predictions
     */
    public function verifyExpectations();

    /**
     * Checks mocked object predictions
     */
    public function verifyServiceExpectationsById($serviceId);

    /**
     * Cleans up services mocks
     */
    public function cleanUpMockedServices();

    /**
     * @param string $id Service Id
     */
    public function unmock($id);
}