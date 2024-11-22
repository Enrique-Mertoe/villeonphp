<?php

use Villeon\DB\DataTypes\DataTypes;
use Villeon\DB\DBOptions;
use Villeon\DB\Model;
use Villeon\DB\VilleonSQL;

require_once("../src/DB/init.php");

VilleonSQL::init_database(
    new DBOptions(
        host: "localhost", user: "bootstrap", password: "", name: "vdb"
    )
);

$User = Model::define("users", [
    "uid" => [
        "type" => DataTypes::STRING(),
        "allowNull" => false,
        "unique" => true
    ],
    "is_active" => [
        "type" => DataTypes::$BOOL,
        "allowNull" => false,
        "default" => false,
    ],
    "email" => [
        "unique" => true,
        "type" => DataTypes::STRING()
    ]
]);


(new VilleonSQL())->build();

//$User->create([
//    "uid" => "9091",
//    "email" => "mike@mike.com",
//    "is_active" => true
//]);

$user = $User->findAll([
    "is_active" => true
]);
