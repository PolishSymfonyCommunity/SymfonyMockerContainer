Symfony Mocker Container
========================

[![Build Status](https://secure.travis-ci.org/PolishSymfonyCommunity/SymfonyMockerContainer.png?branch=master)](http://travis-ci.org/PolishSymfonyCommunity/SymfonyMockerContainer)

Mocker container enables you to mock services in the Symfony dependency
injection container. It is particularly useful in functional tests and
Behat scenarios.

Installation
------------

Add SymfonyMockerContainer to your composer.json:

    {
        "require": {
            "polishsymfonycommunity/symfony-mocker-container": "*"
        }
    }

Replace base container class for test environment in `app/AppKernel.php`::

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

Clear your cache.

Using in Behat steps
--------------------

    namespace PSS\Features\Context;

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
            $this->kernel->mockService('crm.client', 'PSS\Crm\Client')
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

Using in Symfony functional tests
---------------------------------

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
            $this->client->getContainer()->mockService('crm.client', 'PSS\Crm\Client')
                ->shouldReceive('send')
                ->once()
                ->andReturn(true);

            // ...
        }
    }

