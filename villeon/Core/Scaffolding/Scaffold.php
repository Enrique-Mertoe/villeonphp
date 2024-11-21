<?php

namespace Villeon\Core\Scaffolding;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionFunction;
use Villeon\core\Collection\Collection;
use Villeon\Core\Routing\Route;
use Villeon\Http\Request;

class Scaffold
{
    private string $url_method;
    private array $routes;
    private string $endpoint;
    private Collection $error_routes;

    protected function init_routes(): void
    {


        $routes = (new Route())->build();
        $this->routes = Collection::from_array($routes->rules)->array();
        $this->error_routes = Collection::from_array($routes->errors);
        try {
            $this->process_url_path();
        } catch (Exception $exception) {
            exit();
        }
    }

    private function isDefined($v, $s = false): bool
    {

        if (!$s)
            $v = implode("/", $v);
        foreach ($this->routes as $r) {
            if ($r->rule === "/" . $v)
                return true;
        }
        return false;

    }

    protected function dispatch(mixed $rule, callable $callback, $args): void
    {
        try {
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
                $res = call_user_func_array($callback, $args);
                ob_end_clean();
                if (is_string($res))
                    print_r($res);
                elseif (is_array($res))
                    print_r(json_encode($res));
                else
                    echo "function did not return valid response";

            }
        } catch (Exception $e) {

        }
    }

    private function normalizeRule($rule): array|string|null
    {
        return preg_replace('/\s*:\s*/', ':', $rule);
    }

    private function process_endpoint(): void
    {
        $fullUri = urldecode($_SERVER['REQUEST_URI']);
        $fullUri = parse_url($fullUri)["path"];
        $fullUri = preg_replace('#/+#', '/', $fullUri);
        if (($basePath = dirname(urldecode($_SERVER['SCRIPT_NAME']))) != "/")
            $fullUri = str_replace($basePath, '', $fullUri);
        $fullUri = trim($fullUri);
        if (!str_starts_with($fullUri, "/"))
            $fullUri = "/$fullUri";
        $this->endpoint = empty($fullUri) ? '/' : $fullUri;
    }

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

            try {
                $rule = $this->normalizeRule($route->rule);
//                $pattern = '#^' . preg_replace('/<(\w+)>/', '([^/]+)', $route->rule) . '$#';
                $pattern = '#^' . preg_replace('/<(\w+):path>/', '(.+)', preg_replace('/<(\w+)>/', '([^/]+)', $rule)) . '$#';

//                $pattern = '#^' . preg_replace('/\{(\w+)\}/', '([^/]+)', $route->rule) . '$#';

                if (preg_match($pattern, $this->endpoint, $matches)) {
                    $this->checkAllowedMethods($route->method, Request::$method);
                    if (($ar = array_slice($matches, 1)) && $this->isDefined($ar)) {
                        continue;
                    }

                    $this->dispatch($route->rule, $route->controller, array_slice($matches, 1));

                    return;
                }

            } catch (Exception $exception) {

//                error_log("Error processing route: " . $exception->getMessage());
            }

        }
        $this->manage_bad_request();
    }


    private function checkAllowedMethods($rootMethods, $m): void
    {
        if (!in_array($m, $rootMethods))
            self::handleError(403);
    }

    private function isErrorPageDefined($v)
    {
        foreach ($this->error_routes->array() as $r) {
            if ($r->rule === $v) {
                return $r->controller;
            }
        }
        return null;

    }

    private function manage_bad_request(): void
    {
        $route = $this->isErrorPageDefined(404);
        if (is_callable($route)) {
            $this->dispatch(404, $route, []);
            return;
        }
        echo "Page not found";
    }

    #[NoReturn] private static function handleError(int $code): void
    {
        exit("method not allowed");
    }


}
