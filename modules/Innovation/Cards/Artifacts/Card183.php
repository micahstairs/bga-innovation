<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card183 extends AbstractCard
{
  // Roundhay Garden Scene
  // - 3rd edition:
  //   - Meld the highest card from your score pile. Draw and score two cards of value equal to the
  //     melded card. Execute the effects of the melded card as if they were on this card. Do not
  //     share them.
  // - 4th edition:
  //   - Meld the highest card from your score pile. Draw and score two cards of value equal to the
  //     melded card. Self-execute the melded card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::SCORE,
      'meld_keyword'  => true,
      'age'           => self::getMaxValueInLocation(Locations::SCORE),
    ];
  }

  public function afterInteraction()
  {
    $value = 0;
    if (self::getNumChosen() > 0) {
      $value = self::getLastSelectedAge();
    }
    self::drawAndScore($value);
    self::drawAndScore($value);
    if (self::getNumChosen() > 0) {
      if (self::isFirstOrThirdEdition()) {
        self::fullyExecute(self::getLastSelectedCard());
      } else {
        self::selfExecute(self::getLastSelectedCard());
      }
    }
  }

}