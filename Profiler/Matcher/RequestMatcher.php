<?php

namespace Webonaute\SymfonyProfilerMatcherBundle\Profiler\Matcher;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\Routing\Router;
use Webonaute\SymfonyProfilerMatcherBundle\Annotation\Profiler;

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
        $reader = new AnnotationReader();
        $route = $this->router->matchRequest($request);
        list ($controller, $method) = explode("::", $route['_controller']);
        $methodReflection = new \ReflectionMethod($controller, $method);

        /** @var Profiler $classAnnotation */
        $classAnnotation = $reader->getMethodAnnotation(
            $methodReflection, 'Webonaute\SymfonyProfilerMatcherBundle\Annotation\Profiler'
        );

        if ($classAnnotation !== null and $classAnnotation->desable == true){
            //desable profiling on this request.
            return false;
        }

        //keep profiling on.
        return true;
    }
}