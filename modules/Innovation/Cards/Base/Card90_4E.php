<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card90_4E extends AbstractCard
{
  // Satellites (4th edition):
  //   - Return all cards from your hand. You may splay your purple cards up.
  //   - Draw three [8].
  //   - Meld a card from your hand, then self-execute it.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else if (self::isSecondNonDemand()) {
      self::draw(8);
      self::draw(8);
      self::draw(8);
    } else if (self::isThirdNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->return()->all()->fromYourHand()->build();
      } else {
        return self::youMay()->splayUp(Colors::PURPLE)->build();
      }
    } else {
      return self::youMust()->meld()->fromYourHand()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isThirdNonDemand()) {
      self::selfExecute($card);
    }
  }

}