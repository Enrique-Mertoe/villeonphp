<?php

namespace Villeon\Core\Scaffolding;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use ReflectionFunction;
use RuntimeException;
use Villeon\Core\Routing\Route;
use Villeon\Core\Routing\Router;
use Villeon\Core\Routing\RouteRegistry;
use Villeon\Http\Request;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Collection;
use Villeon\Utils\Console;

class Scaffold
{
    private string $url_method;

    /**
     * @var RouteRegistry[]
     */
    private array $blue_prints;

    private string $endpoint;
    private Collection $error_routes;

    /**
     * @return void
     * @throws Exception
     */
    protected function init_routes(): void
    {


        $this->blue_prints = RouteRegistry::build();
        $routes = $this->blue_prints;
        $default = $routes["default"];
        unset($routes["default"]);

        foreach ($this->prepare_routes($default->getBluePrints()->getAll()) as $route) {
            $this->process_route($route);
        }
        $stop = false;
        foreach ($routes as $name => $group) {
            foreach ($this->prepare_routes($group->getBluePrints()->getAll()) as $route) {
                if ($stop = $this->process_route($route)) {
                    return;
                }
            }
        }
        $this->manage_unknown_request();

//        $this->routes = Collection::from_array($routes->rules)->array();
//        $this->error_routes = Collection::from_array($routes->errors);
//        $this->process_url_path();
    }

    /**
     * @param array $routes
     * @return array
     */
    private function prepare_routes(array $routes): array
    {
        $to_sort = $routes;
        usort($to_sort, function ($a, $b) {
            $aStaticParts = substr_count($a->rule, '/') - substr_count($a->rule, '<');
            $bStaticParts = substr_count($b->rule, '/') - substr_count($b->rule, '<');
            return $bStaticParts - $aStaticParts;
        });
        return $to_sort;
    }

    private function process_route(Route $route): bool
    {

        if ($match = $route->match(Request::$path)) {
            if (($ar = array_slice($match, 1)) && $this->isDefined($ar)) {
                return false;
            }

            $this->dispatch($route, $match);
            return true;
        }
        return false;
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

        $found = false;
        foreach ($this->blue_prints as $name => $group) {
            foreach ($this->prepare_routes($group->getBluePrints()->getAll()) as $route) {
                if ($route->rule === "/" . $v)
                    $found = true;
                break;
            }
            if ($found) break;

        }
        return $found;
    }

    /**
     * @param mixed $rule
     * @param callable $callback
     * @param $args
     * @return void
     * @throws ReflectionException
     * @throws Exception|\Throwable
     */
    protected function dispatch(Route $route, array $args = []): void
    {

        $controller = $route->controller;
        $reflection = new ReflectionFunction($controller);
        $reflectionParams = $reflection->getParameters();
        $required = count($reflectionParams);
        $found = count($args);
        if ($required != $found) {
            throw new ParameterCountException($required, $found);
        }
        $defined = $route->required_params;
        foreach ($reflectionParams as $param) {
            $expectedName = $param->getName();
            if (!array_key_exists($expectedName, $defined)) {
                throw new RuntimeException("Missing parameter: $expectedName");
            }
        }
        if (is_callable($route->controller)) {
            ob_start();
            try {
                $res = call_user_func_array($controller, $args);
            } catch (Exception $e) {
                http_response_code(500);
                $res = $e;
            }
            $bufferedOutput = ob_get_contents();
            ob_end_clean();

            Console::Write($bufferedOutput);
            $this->rule_logger($route->rule, http_response_code());
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
//        } catch (Exception $exception) {
//            print_r("ss");
//            throw $exception;
//        } finally {
//            exit();
//        }


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
        $fullUri = trim($fullUri);
        if (!str_starts_with($fullUri, "/"))
            $fullUri = "/$fullUri";
        $this->endpoint = empty($fullUri) ? '/' : $fullUri;
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
     * @return Route|null
     */
    private function is404Defined(): ?Route
    {
        return $this->blue_prints["default"]->getErrorHandlerBluePrint()->get404();
    }

    /**
     * @return void
     * @throws Exception|\Throwable
     */
    private function manage_unknown_request(): void
    {
        http_response_code(404);
        if ($route = $this->is404Defined()) {
            $this->dispatch($route);
        } else {
            ThemeBuilder::$instance->display_404();
        }

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
