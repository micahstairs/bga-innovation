<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card374_3E extends AbstractCard
{

  // Toilet (3rd edition):
  //  - ECHO: Draw and tuck a [4].
  //  - I DEMAND you return all cards from your score pile of value matching the highest bonus on my board!
  //  - You may return a card in your hand and draw a card of the same value.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(4);
    } else if (self::isDemand() || self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      $bonuses = self::getBonuses(self::getLauncherId());
      $value = $bonuses ? max($bonuses) : 0;
      return self::youMust()->return()->all()->value($value)->fromYourScore()->build();
    } else {
      return self::youMay()->return()->fromYourHand()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isNonDemand()) {
      self::draw(self::getValue($card));
    }
  }

}