<?php

namespace spec\PSS\SymfonyMockerContainer\DependencyInjection;

use Mockery\CountValidator\Exception as MockeryException;
use Mockery\MockInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MockeryContainerSpec extends ObjectBehavior
{
    function it_implements_mocker_container_interface()
    {
        $this->shouldImplement('\PSS\SymfonyMockerContainer\DependencyInjection\MockerContainerInterface');
    }

    function it_verifies_service_expectation_by_id(MockInterface $service)
    {
        $this->setMock('service_id', $service);
        $this->verifyServiceExpectationsById('service_id');

        $service->mockery_verify()->shouldHaveBeenCalled();
    }

    function it_throws_an_exception_on_unmet_expectations(MockInterface $service)
    {
        $this->setMock('service_id', $service);
        $exception = new MockeryException();
        $service->mockery_verify()->willThrow($exception);

        $expectedExceptionClass = '\PSS\SymfonyMockerContainer\Exception\ExpectationException';
        $this->shouldThrow($expectedExceptionClass)->duringVerifyServiceExpectationsById('service_id');
    }

    function letGo()
    {
        $this->cleanUpMockedServices();
    }
}
