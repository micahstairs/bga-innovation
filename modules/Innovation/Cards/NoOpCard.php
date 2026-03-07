<?php

namespace Innovation\Cards;

use Innovation\Cards\ExecutionState;

/** No-op for card types with no dogma implementation (e.g. Cities). */
class NoOpCard extends AbstractCard
{
  private int $cardId;

  function __construct(\Innovation $game, ExecutionState $state, int $card_id)
  {
    parent::__construct($game, $state);
    $this->cardId = $card_id;
  }

  protected function getThisCardId(): string
  {
    return (string) $this->cardId;
  }

  public function initialExecution()
  {
    self::setMaxSteps(0);
  }
}
