<?php

namespace Villeon\Core\Content;

use Closure;
use Villeon\Http\Response;

/**
 * Interface MiddlewareHandler
 *
 * Handles internal execution of middleware functions before and after request processing.
 *
 * This interface is used internally by the framework's core (`AppContext`) to execute
 * middleware logic that has been registered via the public API (`beforeRequest`, `afterRequest`).
 *
 * Note: This does not define middleware registration for users, but rather how
 * the framework processes registered middleware during request execution.
 */
interface MiddlewareHandler
{
    /**
     * Executes the internally registered "before request" middleware.
     *
     * This method is triggered before request processing starts.
     * It allows middleware to modify the request, halt execution, or perform pre-processing.
     *
     * @param Closure $f The next function to call after middleware execution.
     *
     * @return void
     */
    public function onBeforeRequest(Closure $f): void;

    /**
     * Executes the internally registered "after request" middleware.
     *
     * This method is triggered after the request has been processed and a response is available.
     * Middleware can modify the response or perform post-processing before sending it to the client.
     *
     * @param Response $response The response object after request processing.
     * @param bool $ignore If true, skips middleware execution and proceeds directly to the callback.
     * @param Closure|null $f The next function to call after middleware execution.
     *
     * @return void
     */
    public function onAfterRequest(Response $response, bool $ignore = false, Closure $f = null): void;
}
