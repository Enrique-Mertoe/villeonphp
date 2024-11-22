<?php

namespace Villeon;


/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */


use Villeon\Core\VilleonBuilder;
use Villeon\DB\DBOptions;
use Villeon\DB\VilleonSQL;


class Application
{
    /**
     * @return void
     */
    public function run(): void
    {
        global $SRC;
        VilleonSQL::init_database(
            new DBOptions(
                host: "localhost", user: "root", password: "", name: "vdb"
            )
        );
        (new VilleonSQL())->build();
        $app = VilleonBuilder::builder();
        $app->theme->initialize($SRC);
        $app->build();

    }
}
