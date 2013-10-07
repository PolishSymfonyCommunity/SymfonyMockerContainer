<?php

namespace PSS\SymfonyMockerContainer\DependencyInjection;

use Mockery\CountValidator\Exception;
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
     * @return mixed
     */
    protected function mockService($classOrInterface)
    {
        return call_user_func_array(array('Mockery', 'mock'), func_get_args());
    }
}
