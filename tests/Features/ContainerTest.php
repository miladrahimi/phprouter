<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpRouter\Tests\Common\SampleClass;
use MiladRahimi\PhpRouter\Tests\Common\SampleConstructorController;
use MiladRahimi\PhpRouter\Tests\Common\SampleInterface;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class ContainerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_binding_and_resolving_with_container()
    {
        $router = $this->router();

        $container = $router->getContainer();
        $container->singleton(SampleInterface::class, SampleClass::class);
        $router->setContainer($container);

        $router->get('/', function (Container $container) {
            return get_class($container->get(SampleInterface::class));
        });

        $router->dispatch();

        $this->assertEquals(SampleClass::class, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_binding_and_resolving_with_controller_method()
    {
        $router = $this->router();

        $container = $router->getContainer();
        $container->singleton(SampleInterface::class, SampleClass::class);
        $router->setContainer($container);

        $router->get('/', function (SampleInterface $sample) {
            return get_class($sample);
        });

        $router->dispatch();

        $this->assertEquals(SampleClass::class, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_binding_and_resolving_with_controller_constructor()
    {
        $router = $this->router();

        $container = $router->getContainer();
        $container->singleton(SampleInterface::class, SampleClass::class);
        $router->setContainer($container);

        $router->get('/', [SampleConstructorController::class, 'getSampleClassName']);

        $router->dispatch();

        $this->assertEquals(SampleClass::class, $this->output($router));
    }
}
