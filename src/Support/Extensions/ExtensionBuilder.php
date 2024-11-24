<?php

namespace Villeon\Support\Extensions;
abstract class ExtensionBuilder
{
    private bool $enabled = true;

    /**
     * @param bool $enabled
     * @return $this
     */
    function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return void
     */
    abstract function build(): void;
}