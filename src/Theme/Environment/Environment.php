<?php
/**
 * Environment.php
 * @package    Villeon\Theme
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Theme\Environment;

use Exception;
use RuntimeException;
use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;
use Villeon\Core\Facade\Env;
use Villeon\Core\Facade\Settings;

/**
 *
 */
class Environment extends \Twig\Environment
{
    /**
     * @param LoaderInterface $loader
     * @param array $options
     */
    public function __construct(LoaderInterface $loader, array $options = [])
    {
        parent::__construct($loader, $options);
        $this->extend_methods();

    }

    /**
     * @return void
     */
    private function extend_methods(): void
    {
        $this->addFunction(new TwigFunction("url_for", function ($endpoint, ...$args) {
            return url_for($endpoint, null, ...($args[0] ?? []));
        }));
        $this->addFunction(new TwigFunction("get_flashed_messages",
                function (bool $with_categories = false, array $category_filter = []) {
                     return get_flashed_messages($with_categories, $category_filter);
                })
        );
    }

    /**
     * @return array[]
     */
    private function prepare_context(): array
    {
        return [
            "app" => [
                "env" => Env::all(),
                "session" => [],
                "settings" => Settings::all()
            ]
        ];
    }

    /**
     * @param $name
     * @param array $context
     * @return string
     */
    public function render($name, array $context = []): string
    {
        try {

            return parent::render($name, array_merge($context, $this->prepare_context()));
        } catch (Exception $e) {
            throw new RuntimeException($e);
        }
    }
}
