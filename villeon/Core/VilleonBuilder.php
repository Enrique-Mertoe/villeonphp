<?php
/**
 * @author Smallville Cycle
 */

namespace Villeon\Core;
require_once("Scaffolding/Scaffold.php");

use Villeon\Core\Scaffolding\Scaffold;
use Villeon\core\Theme\ThemeBuilder;

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
        $this->init_routes();

    }
}
