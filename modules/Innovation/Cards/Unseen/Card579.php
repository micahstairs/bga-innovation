<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card579 extends AbstractCard
{

  // Cryptocurrency:
  //   - Return all cards from your score pile. For each different value of card you return, draw
  //     and score a [10].
  //   - You may splay your red cards up.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      self::setAuxiliaryArray([]);
      return self::youMust()->return()->all()->fromYourScore()->build();
    } else {
      return self::youMay()->splayUp(Colors::RED)->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray(self::getValue($card));
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $values = array_unique(self::getAuxiliaryArray());
      for ($i = 0; $i < count($values); $i++) {
        self::drawAndScore(10);
      }
    }
  }

}