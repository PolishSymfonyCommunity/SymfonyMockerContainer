<?php

namespace spec\PSS\SymfonyMockerContainer\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class MockerContainerSpec extends ObjectBehavior
{
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

        $mock->shouldImplement('\Prophecy\Prophecy\ObjectProphecy');
        $mock->reveal()->shouldBeAnInstanceOf('\StdClass');
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

        $this->get('std_class.mock')->shouldReturn($mock);
    }

    function it_unmocks_service()
    {
        $service = new \StdClass();
        $this->set('std_class.mock', $service);
        $this->mock('std_class.mock', '\StdClass');

        $this->unmock('std_class.mock');

        $this->get('std_class.mock')->shouldReturn($service);
    }

    function it_checks_predictions_of_mocked_services(Prophet $prophet)
    {
        $this->setProphet($prophet);
        $prophet->checkPredictions()->shouldBeCalled();

        $this->checkPredictions();
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
}
