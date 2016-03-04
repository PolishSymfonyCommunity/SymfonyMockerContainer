Symfony Mocker Container
========================

[![Build Status](https://secure.travis-ci.org/PolishSymfonyCommunity/SymfonyMockerContainer.png?branch=master)](http://travis-ci.org/PolishSymfonyCommunity/SymfonyMockerContainer)

Mocker container enables you to mock services in the Symfony dependency
injection container. It is particularly useful in functional tests and
Behat scenarios.

**This package was always a hack. For a better approach try https://github.com/docteurklein/TestDoubleBundle.**

If you want to use the mocker container with Behat try the
[Symfony2 Mocker Extension](https://github.com/PolishSymfonyCommunity/Symfony2MockerExtension).

**Warning:**

Mind that you can only mock services using the BrowserKitDriver (used with Symfony2 functional
tests and Symfony2Extension for Behat). You won't be able to mock services using any driver
that makes actual HTTP request to your applicaction.


Installation
------------

Add SymfonyMockerContainer to your composer.json:

```js
{
    "require": {
        "polishsymfonycommunity/symfony-mocker-container": "*"
    }
}
```

Replace base container class for test environment in `app/AppKernel.php`::

```php
<?php

/**
 * @return string
 */
protected function getContainerBaseClass()
{
    if ('test' == $this->environment) {
        return '\PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer';
    }
    
    return parent::getContainerBaseClass();
}
```

Clear your cache.

Using in Behat steps
--------------------

Use `mock()` method on the container to create a new Mock with Mockery:

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
        \Mockery::close();
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
        foreach ($this->client->getContainer()->getMockedServices() as $id => $service) {
            $this->client->getContainer()->unmock($id);
        }

        \Mockery::close();

        $this->client = null;

        parent::tearDown();
    }

    public function testThatContactDetailsAreSubmittedToTheCrm()
    {
        $this->client->getContainer()->mock('crm.client', 'PSS\Crm\Client')
            ->shouldReceive('send')
            ->once()
            ->andReturn(true);

        // ...
    }
}
```
