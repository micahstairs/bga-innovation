<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card167 extends AbstractCard
{
  // Frigate Constitution
  //   - I COMPEL you to reveal a card in your hand! If you do, and its value is equal to the value
  //     of any of my top cards, return it and all cards of its color from your board!

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->reveal()->fromYourHand()->build();
    } else {
      return self::youMust()->return()->all()->fromYourStack(self::getLastSelectedColor())->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      if (in_array(self::getValue($card), self::getValues(self::getTopCards(self::getLauncherId())))) {
        self::return($card);
        self::setMaxSteps(2);
      } else {
        self::transferToHand($card);
      }
    }
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::HAND) > 0;
  }

}