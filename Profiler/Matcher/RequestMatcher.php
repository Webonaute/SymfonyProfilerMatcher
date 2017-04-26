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

    /**
     * @var
     */
    protected $ttl = 86400;

    /**
     * @var \Redis|\Predis\Client
     */
    protected $cache;

    protected $cacheKeyPrefix = "wbntpm_route_";

    /**
     * RequestMatcher constructor.
     *
     * @param Router $router
     * @param \Redis|\Predis\Client|null $cache
     * @param int $ttl
     */
    public function __construct(Router $router, $cache = null, $ttl = 86400)
    {
        $this->router = $router;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function matches(Request $request)
    {
        $reader = new AnnotationReader();
        $route = $this->router->matchRequest($request);
        $routeName = $route['_route'];

        $cache = $this->getCache($routeName);
        if (is_bool($cache)) {
            return $cache;
        } else {
            $ret = true;
            list ($controller, $method) = explode("::", $route['_controller']);
            $methodReflection = new \ReflectionMethod($controller, $method);

            /** @var Profiler $classAnnotation */
            $classAnnotation = $reader->getMethodAnnotation(
                $methodReflection, 'Webonaute\SymfonyProfilerMatcherBundle\Annotation\Profiler'
            );

            if ($classAnnotation !== null and $classAnnotation->desable == true) {
                //desable profiling on this request.
                $ret = false;
            }

            $this->setCache($routeName, $ret);

            return $ret;
        }

    }

    /**
     * @param $routeName
     *
     * @return null|bool
     */
    protected function getCache($routeName)
    {
        if ($this->cache !== null) {
            $cacheItem = $this->cache->get($this->getCacheKey($routeName));
            if ($cacheItem !== false) {
                return (bool) $cacheItem;
            }
        }

        return null;
    }

    /**
     * @param string $routeName
     * @param bool $value
     */
    protected function setCache($routeName, $value)
    {
        if ($this->cache !== null) {
            //save 0 instead of false to not confuse with no cache = false returned by the \Redis class.
            $this->cache->setex($this->getCacheKey($routeName), $this->ttl, (int) $value);
            $this->cache->exec();
        }
    }

    /**
     * @param $routeName
     *
     * @return string
     */
    protected function getCacheKey($routeName)
    {
        return $this->cacheKeyPrefix.$routeName;
    }
}