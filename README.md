# SymfonyProfilerMatcher

## Simple setup
Allow to easily desable profiler by adding annotation to your controller.

Install the bundle :

```
composer install webonaute/symfony-profiler-matcher "^1.0"
```

Add bundle to kernel.

```
<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use UgroupMedia\Bundle\CommonBundle\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function loadBundles()
    {
        $bundles = array(
          ...
            new Webonaute\SymfonyProfilerMatcherBundle\SymfonyProfilerMatcherBundle(),
        );

        return $bundles;
    }

    ...
}

```

Add annotation to your controller.
```
<?php

namespace AppBundle\Controller\App;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Webonaute\SymfonyProfilerMatcherBundle\Annotation as SPM;

class PingController extends Controller
{
    /**
     * @Route(name="ping", path="/ping")
     * @SPM\Profiler(desable=true)
     * @return Response
     */
    public function pingAction()
    {
        return new Response('OK');
    }
}
```

Add config

```
framework:
    profiler:
        matcher:
            service: webonaute.profiler.request.matcher
```

## Setup with cache.

We support Redis/Predis caching using SNC Redis bundle.

Simply add a service to inject the cache object.
```
services:
    webonaute.profiler.request.matcher.cache:
        class: Webonaute\SymfonyProfilerMatcherBundle\Profiler\Matcher\RequestMatcher
        arguments: ['@router', "@snc_redis.default"]
        public: false
```
Change @snc_redis.default for whatever configuration your use.

Than set the framework profiler matcher to the new service.
```
framework:
    profiler:
        matcher:
            service: webonaute.profiler.request.matcher.cache
```
           
