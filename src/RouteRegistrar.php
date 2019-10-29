<?php

namespace Theomessin\Tus;

use Illuminate\Contracts\Routing\Registrar as Router;

final class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * The tus controller.
     *
     * @var string
     */
    protected $controller;

    /**
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->controller = config('tus.controller');
    }

    /**
     * Register all routes.
     *
     * @return void
     */
    public function all()
    {
        $this->forCoreProtocol();
        $this->forCreationExtension();
    }

    /**
     * Register core tus protocol routes.
     */
    public function forCoreProtocol()
    {
        $controller = $this->controller;

        $this->router->options('/', "{$controller}@options")->name('tus.server');
        $this->router->get('/{upload}', "{$controller}@get")->name('tus.resource');
        $this->router->patch('/{upload}', "{$controller}@patch")->name('tus.patch');
    }

    /**
     * Register tus protocol creation extension routes.
     */
    public function forCreationExtension()
    {
        $controller = $this->controller;
        $this->router->post('/', "{$controller}@post");
    }
}
