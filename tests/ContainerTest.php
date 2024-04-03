<?php

namespace Utopia\Http;

use PHPUnit\Framework\TestCase;
use Utopia\DI\Container;
use Utopia\DI\Dependency;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    protected ?Container $container = null;

    public function setUp(): void
    {
        $this->container = new Container();

        $user = new Dependency();
        $user
            ->setName('user')
            ->dependency('age')
            ->setCallback(fn ($age) => 'John Doe is '.$age.' years old.');
        ;

        $age = new Dependency();
        $age
            ->setName('age')
            ->setCallback(fn () => 25);
        ;

        $this->container
            ->set($user)
            ->set($age)
        ;
    }

    public function testResolution()
    {
        $this->assertEquals('John Doe is 25 years old.', $this->container->get('user'));
    }
}
