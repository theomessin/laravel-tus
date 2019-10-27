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
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register all routes.
     *
     * @return void
     */
    public function all()
    {
        $this->forCoreProtocol();
    }

    /**
     * Register core tus protocol routes.
     */
    public function forCoreProtocol()
    {
        $this->router->options('/', 'CoreProtocolController@options');
        $this->router->get('/{upload}', 'CoreProtocolController@get');
    }
}
