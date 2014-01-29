<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Mockery\CountValidator\Exception;
use Mockery\MockInterface;
use PSS\SymfonyMockerContainer\Exception\ExpectationException;

class MockeryContainer extends AbstractMockerContainer
{
    /**
     * Checks mocked object predictions
     */
    public function verifyServiceExpectationsById($serviceId)
    {
        try {
            self::$mockedServices[$serviceId]->mockery_verify();
        } catch (Exception $exception) {
            throw new ExpectationException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param string $classOrInterface Class or Interface name
     *
     * @return MockInterface
     */
    protected function mockService($classOrInterface)
    {
        return \Mockery::mock($classOrInterface);
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
}
