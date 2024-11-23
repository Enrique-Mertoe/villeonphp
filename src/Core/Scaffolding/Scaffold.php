<?php

namespace Villeon\Core\Scaffolding;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use ReflectionFunction;
use Villeon\Core\Routing\Router;
use Villeon\Http\Request;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Collection;
use Villeon\Utils\Console;

class Scaffold
{
    private string $url_method;
    private array $routes;
    private string $endpoint;
    private Collection $error_routes;

    /**
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    protected function init_routes(): void
    {


        $routes = (new Router())->build();
        $this->routes = Collection::from_array($routes->rules)->array();
        $this->error_routes = Collection::from_array($routes->errors);
        $this->process_url_path();
    }

    /**
     * @param $v
     * @param bool $s
     * @return bool
     */
    private function isDefined($v, bool $s = false): bool
    {

        if (!$s)
            $v = implode("/", $v);
        foreach ($this->routes as $r) {
            if ($r->rule === "/" . $v)
                return true;
        }
        return false;

    }

    /**
     * @param mixed $rule
     * @param callable $callback
     * @param $args
     * @return void
     * @throws ReflectionException
     */
    protected function dispatch(mixed $rule, callable $callback, $args): void
    {
        $rfc = new ReflectionFunction($callback);
        $p = $rfc->getParameters();
        $required = count($p);
        $found = count($args);
        if ($required < $found) {
            exit("Too many parameters passed");
        }
        if ($required > $found) {
            exit("Less parameters passed, required: $required parameters, found: $found");
        }
        $rule = $this->normalizeRule($rule);
        preg_match_all('/<([^>:]+)(?::path)?>/', $rule, $defined);
        foreach ($p as $index => $param) {
            $name_defined = $defined[1][$index];
            $expectedName = $param->getName();

            if ($name_defined !== $expectedName) {
                exit("Undefined $expectedName.  (Expected parameter '$name_defined', found '$expectedName' at position $index.)");
            }
        }
        if (is_callable($callback)) {
            ob_start();
            try {

                $res = call_user_func_array($callback, $args);
            } catch (Exception $e) {
                $res = $e;
            }
            $bufferedOutput = ob_get_contents();
            ob_end_clean();
            $this->rule_logger($rule);
            Console::Write($bufferedOutput);
            if ($res instanceof \Throwable) {
                throw $res;
            }
            if (is_string($res))
                print_r($res);
            elseif (is_array($res))
                print_r(json_encode($res));
            else {
                throw new \Exception("View function did not return valid response: found " .
                    gettype($res));
            }


        }
        exit();

    }

    /**
     * @param $path
     * @param int $status
     * @return void
     */
    private function rule_logger($path, int $status = 200): void
    {
        $method = Request::$method;
        $timestamp = date('Y-m-d H:i:s'); // Get current timestamp
        $logMessage = sprintf('[%s] %s %s -%s', $timestamp, $method, $path, $status);
        if ($status !== 200)
            Console::Warn($logMessage);
        else
            Console::Write($logMessage);
    }

    /**
     * @param $rule
     * @return array|string|null
     */
    private function normalizeRule($rule): array|string|null
    {
        return preg_replace('/\s*:\s*/', ':', $rule);
    }

    /**
     * @return void
     */
    private function process_endpoint(): void
    {
        $fullUri = urldecode($_SERVER['REQUEST_URI']);
        $fullUri = parse_url($fullUri)["path"];

        $fullUri = preg_replace('#/+#', '/', $fullUri);
//        if (($basePath = dirname(urldecode($_SERVER['SCRIPT_NAME']))) != "/")
//            $fullUri = str_replace($basePath, '', $fullUri);
//        print_r($basePath);
        $fullUri = trim($fullUri);
        if (!str_starts_with($fullUri, "/"))
            $fullUri = "/$fullUri";
        $this->endpoint = empty($fullUri) ? '/' : $fullUri;
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function process_url_path(): void
    {
        (new Request())->build();
        $this->url_method = Request::$method;

        $this->process_endpoint();
        usort($this->routes, function ($a, $b) {
            $aStaticParts = substr_count($a->rule, '/') - substr_count($a->rule, '<');
            $bStaticParts = substr_count($b->rule, '/') - substr_count($b->rule, '<');
            return $bStaticParts - $aStaticParts;
        });
        foreach ($this->routes as $route) {


            $rule = $this->normalizeRule($route->rule);
            $pattern = '#^' . preg_replace('/<(\w+):path>/', '(.+)', preg_replace('/<(\w+)>/', '([^/]+)', $rule)) . '$#';


            if (preg_match($pattern, $this->endpoint, $matches)) {
                $this->checkAllowedMethods($route->method, Request::$method);
                if (($ar = array_slice($matches, 1)) && $this->isDefined($ar)) {
                    continue;
                }

                $this->dispatch($route->rule, $route->controller, array_slice($matches, 1));

                return;
            }


        }
        $this->manage_bad_request();
    }


    /**
     * @param $rootMethods
     * @param $m
     * @return void
     */
    private function checkAllowedMethods($rootMethods, $m): void
    {
        if (!in_array($m, $rootMethods))
            self::handleError(403);
    }

    /**
     * @param $v
     * @return null
     */
    private function isErrorPageDefined($v)
    {
        foreach ($this->error_routes->array() as $r) {
            if ($r->rule === $v) {
                return $r->controller;
            }
        }
        return null;

    }

    /**
     * @return void
     * @throws Exception
     */
    private function manage_bad_request(): void
    {
        $route = $this->isErrorPageDefined(404);
        if (is_callable($route)) {
            $this->dispatch(404, $route, []);
            return;
        }
        $this->rule_logger(Request::$path, 404);
        ThemeBuilder::$instance->display_404();
    }

    /**
     * @param int $code
     * @return void
     */
    #[NoReturn] private static function handleError(int $code): void
    {
        exit("method not allowed");
    }


}
