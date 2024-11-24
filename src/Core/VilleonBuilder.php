<?php
/**
 * @author Smallville Cycle
 */

namespace Villeon\Core;
require "Exceptions/ExceptionHandler.php";

use Villeon\Config\ConfigBuilder;
use Villeon\Core\Facade\Facade;
use Villeon\Core\Scaffolding\Scaffold;
use Villeon\Http\Request;
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
        $this->make_config();
        (new Request)->build();
        $this->getConfig()->merge_imported();
        $this->init_routes();

    }

    function getConfig(): ConfigBuilder
    {
        return Facade::getFacade("config");
    }

    function make_config(): void
    {
        session_start();
        set_exception_handler('ExceptionHandler');
//        set_error_handler('customErrorHandler');
    }
}
