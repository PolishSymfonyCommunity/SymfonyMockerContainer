Symfony Mocker Container
========================

[![Build Status](https://secure.travis-ci.org/PolishSymfonyCommunity/SymfonyMockerContainer.png?branch=master)](http://travis-ci.org/PolishSymfonyCommunity/SymfonyMockerContainer)

Mocker container enables you to mock services in the Symfony dependency
injection container. It is particularly useful in functional tests and
Behat scenarios.

If you want to use the mocker container with Behat try the
[Symfony2 Mocker Extension](https://github.com/PolishSymfonyCommunity/Symfony2MockerExtension).

Installation
------------

Add SymfonyMockerContainer to your composer.json:

```js
{
    "require": {
        "polishsymfonycommunity/symfony-mocker-container": "~2.0.0"
    }
}
```

Choose mocking framework, if you prefer to use [Prophecy](https://github.com/phpspec/prophecy) add following require to composer.json:

```js
{
    "require": {
        "phpspec/prophecy": ">=1.0.0"
    }
}
```

If you prefer [Mockery](https://github.com/padraic/mockery) you need to add instead:

```js
{
    "require": {
        "mockery/mockery": ">=0.7.0"
    }
}
```

*You have to use one of those mocking framework, otherwise MockerContainer will not work properly.*


Replace base container class with appropriate container for test environment in `app/AppKernel.php`:

```php
<?php

/**
 * @return string
 */
protected function getContainerBaseClass()
{
    if ('test' == $this->environment) {
        return '\PSS\SymfonyMockerContainer\DependencyInjection\ProphecyContainer'; // For Prophecy integration
//      return '\PSS\SymfonyMockerContainer\DependencyInjection\MockeryContainer';  // For Mockery integration
    }

    return parent::getContainerBaseClass();
}
```



Clear your cache.

Using in Behat steps
--------------------

Use `mock()` method on the container to create a new Mock with Prophecy or Mockery:

```php
<?php

namespace PSS\Features\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class AcmeContext extends BehatContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel = null;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     *
     * @return null
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^CRM API is available$/
     *
     * @return null
     */
    public function crmApiIsAvailable()
    {
        // Mock declaration with Prophecy
        $this->kernel->getContainer()
            ->mock('crm.client', 'PSS\Crm\Client')
            ->send()
            ->willReturn(true);

        // Mock declaration with Mockery
        $this->kernel->getContainer()
            ->mock('crm.client', 'PSS\Crm\Client')
            ->shouldReceive('send')
            ->once()
            ->andReturn(true);
    }

    /**
     * @AfterScenario
     *
     * @return null
     */
    public function verifyPendingExpectations()
    {
        $this->kernel->getContainer()->verifyExpectations();
    }
}
```

Once service is mocked the container will return its mock instead of a real
service.

Using in Symfony functional tests
---------------------------------

```php
<?php

namespace PSS\Bundle\AcmeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AcmeControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client $client
     */
    private $client = null;

    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function tearDown()
    {
        $this->client->getContainer()->verifyExpectations();
        $this->client->getContainer()->cleanUpMockedServices();

        $this->client = null;

        parent::tearDown();
    }

    public function testThatContactDetailsAreSubmittedToTheCrm()
    {
        $this->client->getContainer()
            ->mock('crm.client', 'PSS\Crm\Client')
            ->send()
            ->willReturn(true);

        // ...
    }
}
```
