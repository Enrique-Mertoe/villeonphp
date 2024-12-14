<?php

namespace Villeon\Core\Content;

use Villeon\Theme\Environment\Environment;

class AppContext extends Context
{
    public function getEnv(): Environment
    {
        return $this->theme->getRenderEnv();
    }
    public function buildThrowable(\Throwable $error): string
    {
        return $this->theme->display_error($error);
    }
    public function getErrorContent(int $code): string
    {
        return $this->theme->display_error_page($code);
    }
}
