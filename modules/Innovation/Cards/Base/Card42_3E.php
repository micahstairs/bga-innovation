<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card42_3E extends AbstractCard
{
  // Perspective (3rd edition):
  //   - You may return a card from your hand. If you do, score a card from your hand for every
  //     two [CONCEPT] on your board.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->return()->fromYourHand()->build();
    } else {
      $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::CONCEPT), 2);
      return self::youMay()->score()->exactly($numCards)->fromYourHand()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}