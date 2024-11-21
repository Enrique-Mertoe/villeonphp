<?php

namespace Villeon;


/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */


use Villeon\Core\VilleonBuilder;


class Application
{
    /**
     * @return void
     */
    public function run(): void
    {
        global $SRC;
        $app = VilleonBuilder::builder();
        $app->theme->initialize($SRC);
        $app->build();
    }
}
