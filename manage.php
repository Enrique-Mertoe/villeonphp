#!/usr/bin/env php
<?php
/**
 * SmallVille commandline utility for administrative tasks.
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */


use Villeon\Manager\CommandLine;

require("villeon/Manager/Init.php");
CommandLine::run_command_line($argv);
exit(0);
