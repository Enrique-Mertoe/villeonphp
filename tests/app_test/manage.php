<?php

use Villeon\Manager\CommandLine;

require "../../vendor/autoload.php";
require "smv-config.php";
CommandLine::run_command_line($argv);
exit(0);