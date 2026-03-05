<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card80 extends AbstractCard
{
  // Mass Media:
  //   - You may return a card from your hand. If you do, choose a value, and return all cards of
  //     that value from all score piles.
  //   - You may splay your purple cards up.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMay()->return()->fromYourHand();
      } else if (self::isSecondInteraction()) {
        return self::youMust()->chooseValue();
      } else {
        return self::youMust()->return()->all()->value(self::getAuxiliaryValue())->fromAnyScore();
      }
    } else {
      return self::youMay()->splayUp(Colors::PURPLE);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      self::setMaxSteps(3);
    }
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value); // Remember which value was chosen
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::canSplay(Colors::PURPLE);
  }

}