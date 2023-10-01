<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card214_3E extends AbstractCard
{
  // Twister (3rd edition):
  //   - I COMPEL you to reveal your score pile! For each color, meld a card of that color from
  //     your score pile!

  public function initialExecution()
  {
    self::revealScorePile();
    self::setAuxiliaryArray(self::getUniqueColors(Locations::SCORE)); // Track colors to meld
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $colors = self::getAuxiliaryArray();
    if (!$colors) {
      return [];
    }
    return [
      'location_from' => 'score',
      'meld_keyword'  => true,
      'color'         => $colors,
    ];
  }
  
  public function handleCardChoice(array $card) {
    self::removeFromAuxiliaryArray($card['color']);
    self::setNextStep(1);
  }

}