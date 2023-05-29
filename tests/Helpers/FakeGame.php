<?php

namespace Helpers;

use Innovation\GameInterface;

class FakeGame implements GameInterface {

    // TODO(LATER): Initialize random game state (need to be able to place constraints on it - e.g. at least certain number of cards in the decks)

    // TODO(LATER): Put logic in these functions once we are storing the game's state in-memory.
    public function executeDraw(int $playerId, int $age) { }
    public function executeDrawAndScore(int $playerId, int $age) { }
}
