<?php

namespace Innovation\Cards;

use Innovation\GameInterface;
use Innovation\Cards\ExecutionState;

/* Abstract class of all card implementations */
abstract class Card {

    protected GameInterface $game;

    function __construct(GameInterface $game) {
      $this->game = $game;
    }

    // functions:
    // - setup (doesn't exist in current implementation but we could probably simplify some code if we had this)
    // - logic before interaction (and get number of max steps, and next step which is usually 1)
    // - choice prompt
    // - response to special choice made
    // - no-op detection methods (sharing, compel, demand)

    public abstract function initialExecution(ExecutionState $state);

    public function getInteractionOptions(ExecutionState $state): Array {
        // Subclasses are expected to override this method if the card has any interactions.
        return [];
    }

    public function afterInteraction(ExecutionState $state) {
        // Subclasses are expected to override this method if the card has any interactions.
    }

}
