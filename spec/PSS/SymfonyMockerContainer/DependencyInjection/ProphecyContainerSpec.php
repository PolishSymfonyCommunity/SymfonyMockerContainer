<?php

namespace spec\PSS\SymfonyMockerContainer\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\AggregateException;
use Prophecy\Prophecy\ObjectProphecy;
use PSS\SymfonyMockerContainer\Exception\ExpectationException;

class ProphecyContainerSpec extends ObjectBehavior
{
    function it_implements_mocker_container_interface()
    {
        $this->shouldImplement('\PSS\SymfonyMockerContainer\DependencyInjection\MockerContainerInterface');
    }

    function it_returns_empty_array_if_no_services_have_been_mocked()
    {
        $this->getMockedServices()->shouldReturn(array());
    }

    function it_returns_mocked_services_collection()
    {
        $this->set('std_class.mock', new \StdClass);
        $mock = $this->mock('std_class.mock', '\StdClass');

        $this->getMockedServices()->shouldReturn(array('std_class.mock' => $mock));
    }

    function it_returns_true_if_service_exists()
    {
        $this->set('std_class.mock', new \StdClass);

        $this->has('std_class.mock')->shouldReturn(true);
    }

    function it_returns_true_if_is_mocked()
    {
        $this->set('std_class.mock', new \StdClass);
        $this->mock('std_class.mock', '\StdClass');

        $this->has('std_class.mock')->shouldReturn(true);
    }

    function it_throws_an_exception_if_mocking_unexisting_service()
    {
        $invalidArgumentException = new \InvalidArgumentException('Cannot mock unexisting service: "unexisting.mock"');
        $this->shouldThrow($invalidArgumentException)->duringMock('unexisting.mock', '\StdClass');
    }

    function it_mocks_service_in_the_container()
    {
        $this->set('std_class.mock', new \StdClass);

        $mock = $this->mock('std_class.mock', '\StdClass');

        $mock->shouldBeAnInstanceOf('\Prophecy\Prophecy\ObjectProphecy');
    }

    function it_doesnt_mock_service_twice()
    {
        $this->set('std_class.mock', new \StdClass);
        $mock = $this->mock('std_class.mock', '\StdClass');

        $this->mock('std_class.mock', '\StdClass')->shouldReturn($mock);
    }

    function it_returns_mocked_service()
    {
        $this->set('std_class.mock', new \StdClass);
        $mock = $this->mock('std_class.mock', '\StdClass');

        $this->get('std_class.mock')->shouldReturnAnInstanceOf('\StdClass');
    }

    function it_unmocks_service()
    {
        $service = new \StdClass();
        $this->set('std_class.mock', $service);
        $this->mock('std_class.mock', '\StdClass');

        $this->unmock('std_class.mock');

        $this->get('std_class.mock')->shouldReturn($service);
    }

    function it_verifies_expectations_of_mocked_services(ObjectProphecy $service1, ObjectProphecy $service2)
    {
        $this->setMock('service_1', $service1);
        $this->setMock('service_2', $service2);

        $service1->checkProphecyMethodsPredictions()->shouldBeCalled();
        $service2->checkProphecyMethodsPredictions()->shouldBeCalled();

        $this->verifyExpectations();
    }

    function it_cleans_container()
    {
        $service1 = new \StdClass();
        $service2 = new \StdClass();
        $this->set('std_class.mock_1', $service1);
        $this->set('std_class.mock_2', $service2);
        $this->mock('std_class.mock_1', '\StdClass');
        $this->mock('std_class.mock_2', '\StdClass');

        $this->cleanUpMockedServices();

        $this->get('std_class.mock_1')->shouldReturn($service1);
        $this->get('std_class.mock_2')->shouldReturn($service2);
    }

    function it_verifies_service_expectation_by_id(ObjectProphecy $service)
    {
        $this->setMock('service_id', $service);
        $this->verifyServiceExpectationsById('service_id');

        $service->checkProphecyMethodsPredictions()->shouldHaveBeenCalled();
    }

    function it_throws_an_exception_on_unmet_expectations(ObjectProphecy $service)
    {
        $this->setMock('service_id', $service);
        $exception = new AggregateException();
        $service->checkProphecyMethodsPredictions()->willThrow($exception);

        $expectedExceptionClass = '\PSS\SymfonyMockerContainer\Exception\ExpectationException';
        $this->shouldThrow($expectedExceptionClass)->duringVerifyServiceExpectationsById('service_id');
    }

    function letGo()
    {
        $this->cleanUpMockedServices();
    }
}
