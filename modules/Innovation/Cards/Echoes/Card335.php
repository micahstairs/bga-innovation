<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card335 extends AbstractCard
{

  // Plumbing
  // - 3rd edition:
  //   - ECHO: Score a bottom card from your board.
  //   - No effect.
  // - 4th edition:
  //   - ECHO: Score the bottom blue card from your board.
  //   - Junk all cards in the [1] deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand() && self::isFourthEdition()) {
      self::junkBaseDeck(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFourthEdition()) {
      return self::youMust()->score()->withColor(Colors::BLUE)->fromBottom()->fromYourBoard();
    } else {
      return self::youMust()->score()->fromBottom()->fromYourBoard();
    }
  }

}