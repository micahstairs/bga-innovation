<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card406_4E extends AbstractCard
{

  // X-Ray (4th edition):
  //   - ECHO: Draw and tuck an [8].
  //   - Choose a value. For every color on your board with [HEALTH], draw a card of that value.
  //     Foreshadow any number of them.
  //   - Return all cards from your hand.
  //   - You may splay your yellow cards up.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(8);
    } else if (self::isFirstNonDemand()) {
      $numCardsToDraw = self::countColorsWithIcon(Icons::HEALTH);
      if ($numCardsToDraw > 0) {
        self::setAuxiliaryValue($numCardsToDraw);
        self::setMaxSteps(2);
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isThirdNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->chooseValue()->build();
      } else {
        return self::youMay()->foreshadow()->minCards(1)->maxCards(self::getAuxiliaryValue())->onlyCardsInAuxiliaryArray()->fromYourHand()->build();
      }
    } else if (self::isSecondNonDemand()) {
      return self::youMust()->return()->all()->fromYourHand()->build();
    } else {
      return self::youMay()->splayUp(Colors::YELLOW)->build();
    }
  }

  public function handleValueChoice(int $value)
  {
    $numCardsToDraw = self::getAuxiliaryValue();
    $cardIds = [];
    for ($i = 0; $i < $numCardsToDraw; $i++) {
      $card = self::draw($value);
      $cardIds[] = self::getId($card);
    }
    self::setAuxiliaryArray($cardIds); // Track cards which are allowed to be foreshadowed
  }

}