<?php

namespace Helpers;

trait TestHelpers
{
    /**
     * @return array
     */
    protected function loadGameInfo(): array
    {
        require 'gameinfos.inc.php';
        return $gameinfos;
    }

    /**
     * @return array
     */
    protected function loadGameOptions(): array
    {
        return json_decode(file_get_contents('gameoptions.jsonc'), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array
     */
    protected function loadGamePreferences(): array
    {
        return json_decode(file_get_contents('gamepreferences.jsonc'), true, 512, JSON_THROW_ON_ERROR);
    }
}
