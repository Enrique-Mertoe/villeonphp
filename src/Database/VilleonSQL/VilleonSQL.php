<?php

namespace Villeon\Database\VilleonSQL;
use Villeon\Database\VilleonSQL\Connection\DBOptions;

class VilleonSQL
{
    /**
     * @var DBOptions|null
     */
    static ?DBOptions $options = null;

    /**
     * @param DBOptions $options
     * @return void
     */
    public static function init_database(DBOptions $options): void
    {
        self::$options = $options;
    }

    /**
     * @return void
     */
    public function build(): void
    {
        Model::init_model();
    }
}
