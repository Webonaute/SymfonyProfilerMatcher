<?php

namespace Webonaute\SymfonyProfilerMatcherBundle\Profiler\Matcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\Routing\Router;

class RequestMatcher implements RequestMatcherInterface
{
    /**
     * @var Router
     */
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function matches(Request $request)
    {
        return true;
    }
}