<?php

namespace PSS\SymfonyMockerContainer\Tests\DependencyInjection;

use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;

class MockerContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer $container
     */
    private $container = null;

    /**
     * @var array $services
     */
    private $services = array();

    public function setUp()
    {
        $this->container = new MockerContainer();
        $this->services = array('test.service_1' => null, 'test.service_2' => null, 'test.service_3' => null);

        foreach (array_keys($this->services) as $id) {
            $service = new \stdClass();
            $service->id = $id;

            $this->services[$id] = $service;
            $this->container->set($id, $service);
        }
    }

    /**
     * As the mocks are never cleared during the execution
     * we have to do it manually.
     */
    public function tearDown()
    {
        $reflection = new \ReflectionClass('PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer');
        $property = $reflection->getProperty('mockedServices');
        $property->setAccessible(true);
        $property->setValue(null, array());
    }

    public function testThatBehaviorDoesNotChangeByDefault()
    {
        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertTrue($this->container->has('test.service_2'));
        $this->assertTrue($this->container->has('test.service_3'));
        $this->assertSame($this->services['test.service_1'], $this->container->get('test.service_1'));
        $this->assertSame($this->services['test.service_2'], $this->container->get('test.service_2'));
        $this->assertSame($this->services['test.service_3'], $this->container->get('test.service_3'));
    }

    public function testThatServiceCanBeMocked()
    {
        $mock = $this->container->mock('test.service_1', 'stdClass');

        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertNotSame($this->services['test.service_1'], $mock);
        $this->assertSame($mock, $this->container->get('test.service_1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot mock a non-existent service: "test.new_service"
     */
    public function testThatServiceCannotBeMockedIfItDoesNotExist()
    {
        $this->container->mock('test.new_service', 'stdClass');
    }

    public function testThatMultipleInstancesShareMockedServices()
    {
        $mock = $this->container->mock('test.service_1', 'stdClass');
        $secondContainer = new MockerContainer();

        $this->assertTrue($secondContainer->has('test.service_1'));
        $this->assertFalse($secondContainer->has('test.service_2'));
        $this->assertFalse($secondContainer->has('test.service_3'));
        $this->assertSame($mock, $secondContainer->get('test.service_1'));
        $this->assertNull($secondContainer->get('test.service_2', MockerContainer::NULL_ON_INVALID_REFERENCE));
        $this->assertNull($secondContainer->get('test.service_3', MockerContainer::NULL_ON_INVALID_REFERENCE));
    }

    public function testThatMockedServicesAreAccessible()
    {
        $mock1 = $this->container->mock('test.service_1', 'stdClass');
        $mock2 = $this->container->mock('test.service_2', 'stdClass');

        $mockedServices = $this->container->getMockedServices();

        $this->assertEquals(array('test.service_1' => $mock1, 'test.service_2' => $mock2), $mockedServices);
    }

    public function testThatServiceCanBeMockedOnce()
    {
        $mock1 = $this->container->mock('test.service_1', 'stdClass');
        $mock2 = $this->container->mock('test.service_1', 'stdClass');

        $this->assertSame($mock1, $mock2);
        $this->assertSame($mock2, $this->container->get('test.service_1'));
    }

    public function testThatMockCanBeRemovedAndContainerFallsBackToTheOriginalService()
    {
        $mock = $this->container->mock('test.service_1', 'stdClass');

        $this->container->unmock('test.service_1');

        $this->assertTrue($this->container->has('test.service_1'));
        $this->assertEquals($this->services['test.service_1'], $this->container->get('test.service_1'));
    }
}
