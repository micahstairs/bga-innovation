<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card115 extends AbstractCard
{

  // Pavlovian Tusk
  //   - Draw three cards of value equal to your top green card. Return one of the drawn cards.
  //     Score one of the drawn cards.

  public function initialExecution()
  {
    $card = self::getTopCardOfColor(Colors::GREEN);
    $value = self::getFaceupValue($card);
    $cardIds = [];
    for ($i = 0; $i < 3; $i++) {
      $card = self::draw($value);
      $cardIds[] = self::getId($card);
    }
    self::setAuxiliaryArray($cardIds);
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->onlyCardsInAuxiliaryArray()->fromYourHand()->build();
    } else {
      return self::youMust()->score()->onlyCardsInAuxiliaryArray()->fromYourHand()->build();
    }
  }

}