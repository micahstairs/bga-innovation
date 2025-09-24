<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card591 extends AbstractCard
{

  // Escape Room:
  //   - I DEMAND you draw, reveal, and score an [11]! Score a card from your hand of the same
  //     color as the drawn card! If you don't, you lose!
  //   - Score four top non-yellow cards each with [EFFICIENCY] of different colors on your board.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $card = self::drawAndReveal(11);
      self::setAuxiliaryValue(self::getColor($card)); // Tracks which color needs to be scored
      self::score($card);
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->score()->withColor(self::getAuxiliaryValue())->fromYourHand()->revealingIfUnable()->build();
    } else {
      return self::youMust()->score()->exactly(4)->non(Colors::YELLOW)->withIcon(Icons::EFFICIENCY)->fromYourBoard()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::getNumChosen() === 0) {
      self::lose();
    }
  }

}