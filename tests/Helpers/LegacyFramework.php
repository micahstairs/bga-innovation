<?php

namespace Helpers;

/**
 * Test doubles for framework services that the workbench stub does not provide.
 * Production tables already have $this->bga and getCurrentMainState().
 */
class LegacyPlayerCounter
{
    /** @var object */
    private $game;

    /** @var string */
    private $column;

    public function __construct($game, string $column)
    {
        $this->game = $game;
        $this->column = $column;
    }

    public function get(int $playerId): int
    {
        return (int) $this->game->getUniqueValueFromDB(
            "SELECT {$this->column} FROM player WHERE player_id=" . $playerId
        );
    }

    public function set(int $playerId, int $value, $message = null): int
    {
        $this->game->DbQuery(
            "UPDATE player SET {$this->column} = {$value} WHERE player_id=" . $playerId
        );
        return $value;
    }

    public function getAll(): array
    {
        $rows = $this->game->getCollectionFromDb("SELECT player_id, {$this->column} FROM player");
        $scores = array();
        foreach ($rows as $playerId => $row) {
            $scores[$playerId] = (int) $row[$this->column];
        }
        return $scores;
    }
}

class LegacyBga
{
    /** @var LegacyPlayerCounter */
    public $playerScore;

    /** @var LegacyPlayerCounter */
    public $playerScoreAux;

    public function __construct($game)
    {
        $this->playerScore = new LegacyPlayerCounter($game, 'player_score');
        $this->playerScoreAux = new LegacyPlayerCounter($game, 'player_score_aux');
    }
}

class LegacyMainState
{
    /** @var string */
    public $name;

    public function __construct(array $state)
    {
        $this->name = $state['name'];
    }
}

class LegacyGamestate
{
    /** @var object */
    private $inner;

    public function __construct($inner)
    {
        $this->inner = $inner;
    }

    public function getCurrentMainState(): LegacyMainState
    {
        return new LegacyMainState($this->inner->state());
    }

    public function __call(string $name, array $args)
    {
        return $this->inner->$name(...$args);
    }
}
