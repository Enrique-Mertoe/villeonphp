<?php
/**
 * @author Smallville Cycle
 */

namespace Villeon\Core;
require "Exceptions/ExceptionHandler.php";

use Villeon\Config\ConfigBuilder;
use Villeon\Core\Facade\Facade;
use Villeon\Core\Routing\Router;
use Villeon\Core\Scaffolding\Scaffold;
use Villeon\Theme\ThemeBuilder;

class VilleonBuilder extends Scaffold
{

    public ThemeBuilder $theme;

    public function __construct()
    {
        $this->theme = new ThemeBuilder();
    }

    public static function builder(): VilleonBuilder
    {
        return new VilleonBuilder();
    }

    function build(): void
    {
        $this->getConfig()->merge_imported();
        $this->error_config();
        $this->init_routes();

    }

    function getConfig(): ConfigBuilder
    {
        return Facade::getFacade("config");
    }

    function error_config(): void
    {
        set_exception_handler('ExceptionHandler');
//        set_error_handler('customErrorHandler');
    }
}
