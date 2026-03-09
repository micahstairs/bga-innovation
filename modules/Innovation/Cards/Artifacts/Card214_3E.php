<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card214_3E extends AbstractCard
{
  // Twister (3rd edition):
  //   - I COMPEL you to reveal your score pile! For each color, meld a card of that color from
  //     your score pile!

  public function initialExecution()
  {
    self::revealScorePile();
    self::setAuxiliaryArray(self::getUniqueColorsInLocation(Locations::SCORE)); // Track colors to meld
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $colors = self::getAuxiliaryArray();
    if (!$colors) {
      return self::noInteraction();
    }
    return self::youMust()->meld()->fromYourScore()->withColor($colors);
  }

  public function handleCardChoice(array $card)
  {
    self::removeFromAuxiliaryArray(self::getColor($card));
    self::setNextStep(1);
  }

  public function compelMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}