<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card513 extends AbstractCard
{
  // Masquerade
  //   - Safeguard an available achievement of value equal to the number of cards in your hand. If
  //     you do, return all cards of that value from your hand. If you return a [4], claim the
  //     Anonymity achievement.
  //   - You may splay your purple cards left.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        $value = self::countCards(Locations::HAND);
        self::setAuxiliaryValue($value);
        return self::youMust()->safeguard()->value($value)->fromAvailableAchievements();
      } else {
        return self::youMust()->return()->all()->value(self::getAuxiliaryValue())->fromYourHand();
      }
    } else {
      return self::youMay()->splayLeft(Colors::PURPLE);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        self::setMaxSteps(2);
      } else if (self::isSecondInteraction() && self::getValue($card) === 4) {
        self::claim(CardIds::ANONYMITY);
      }
    }
  }

}