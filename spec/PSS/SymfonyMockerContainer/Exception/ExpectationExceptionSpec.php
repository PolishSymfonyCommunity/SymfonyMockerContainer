<?php

namespace spec\PSS\SymfonyMockerContainer\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExpectationExceptionSpec extends ObjectBehavior
{
    function it_is_a_runtime_exception()
    {
        $this->shouldHaveType('\RuntimeException');
    }
}
