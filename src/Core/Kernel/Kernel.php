<?php

namespace Villeon\Core\Kernel;

use Throwable;
use Villeon\Core\Content\AppContext;
use Villeon\Core\Content\AppEventHandler;
use Villeon\Core\Content\Context;
use Villeon\Core\Content\MiddlewareHandler;
use Villeon\Http\Response;

class Kernel extends Scaffold
{

    private AppEventHandler $eventHandler;
    private MiddlewareHandler $wareResolver;

    public function __construct(AppContext        $context,
                                AppEventHandler   $eventHandler,
                                MiddlewareHandler $wareResolver)
    {
        $this->context = $context;
        $this->eventHandler = $eventHandler;
        $this->wareResolver = $wareResolver;
        $this->registerErrorHandler();

    }

    public static function resolve(
        AppContext        $context,
        AppEventHandler   $errorHandler,
        MiddlewareHandler $wareResolver): void
    {
        (new Kernel($context, $errorHandler, $wareResolver))->organize();
    }

    private function organize(): void
    {
        session_start();
        $this->launch();

    }

    protected function middleWare(string $type, \Closure $f): void
    {
        if ($type === "before") {
            $this->wareResolver->onBeforeRequest($f);
        }
    }

    private function registerErrorHandler(): void
    {
        set_exception_handler(function (Throwable $throwable) {
            if (env("DEBUG"))
                $cont = $this->context->buildThrowable($throwable);
            else
                $cont = $this->context->getErrorContent(500);
            $this->eventHandler->onError($this->build_response($cont, 500, $throwable));
        });
    }

    private function build_response(
        string     $content,
        int        $code = 200,
        ?Throwable $error = null
    ): Response
    {
        return (new Response($content, $code))->setError($error);
    }

    public function onSuccess(string $content): void
    {
        $this->context->response = $this->build_response($content);
    }

    public function onFail(int $code, string $content): void
    {
        $this->context->response = $this->build_response($content, $code);
    }

    public function onResponse(Response $response): int
    {
        $this->context->response = $response;
        return 0;
    }
}
