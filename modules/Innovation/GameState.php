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
    public function inc(string $key, int $value = 1)
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
}
