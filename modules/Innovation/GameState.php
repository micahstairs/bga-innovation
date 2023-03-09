<?php

namespace Innovation;

use Innovation\Utils\Arrays;

class GameState
{
    /** @var \Table $game */
    private $game;

    /**
     * @param \Table $game
     */
    public function __construct($game)
    {
        $this->game = $game;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->game->getGameStateValue($key);
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function set(string $key, $value)
    {
        return $this->game->setGameStateValue($key, $value);
    }

    /**
     * Increment a value for a key
     *
     * @param string $key
     * @param int $value
     * @return mixed
     */
    public function increment(string $key, int $value = 1)
    {
        return $this->game->incGameStateValue($key, $value);
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function setInitial(string $key, $value)
    {
        return $this->game->setGameStateInitialValue($key, $value);
    }

    /**
     * @param string $key
     * @param array $array
     * @return void
     */
    public function setFromArray(string $key, array $array)
    {
        $this->set($key, Arrays::getArrayAsValue($array));
    }

    /**
     * @param string $key
     * @return array
     */
    public function getAsArray(string $key): array
    {
        return Arrays::getValueAsArray($this->get($key));
    }

    /**
     * @return bool
     */
    public function usingFirstEditionRules()
    {
        return $this->get('game_rules') == 2;
    }

    /**
     * @return bool
     */
    public function usingThirdEditionRules()
    {
        return $this->get('game_rules') == 1;
    }

    /**
     * @return bool
     */
    public function usingFourthEditionRules()
    {
        return $this->get('game_rules') == 3;
    }

    /**
     * @return int
     */
    public function getEdition()
    {
        $value = $this->get('game_rules');
        if ($value == 2) {
            return 1;
        }
        if ($value == 1) {
            return 3;
        }
        return 4;
    }

    /**
     * @return bool
     */
    public function artifactsExpansionEnabled()
    {
        return $this->get('artifacts_mode') > 1;
    }

    /**
     * @return bool
     */
    public function artifactsExpansionEnabledWithRelics()
    {
        return $this->get('artifacts_mode') == 3;
    }

    /**
     * @return bool
     */
    public function citiesExpansionEnabled()
    {
        return $this->get('cities_mode') > 1;
    }

    /**
     * @return bool
     */
    public function echoesExpansionEnabled()
    {
        return $this->get('echoes_mode') > 1;
    }
}
