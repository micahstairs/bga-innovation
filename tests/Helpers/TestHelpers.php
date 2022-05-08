<?php

namespace Helpers;

trait TestHelpers
{
    /**
     * @return array
     */
    protected function loadGameInfo() : array
    {
        require 'gameinfos.inc.php';
        return $gameinfos;
    }

    /**
     * @return array
     */
    protected function loadGameOptions() : array
    {
        require 'gameoptions.inc.php';
        return $game_options;
    }

    /**
     * @return array
     */
    protected function loadGamePreferences() : array
    {
        require 'gameoptions.inc.php';
        return $game_preferences;
    }
}
