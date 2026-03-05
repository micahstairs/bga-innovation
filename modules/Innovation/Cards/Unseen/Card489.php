<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

class Card489 extends AbstractCard
{
  // Handshake
  //   - I DEMAND you transfer all cards from my hand to your hand! Choose two colors of cards in
  //     your hand! Transfer all cards in your hand of those colors to my hand!

  public function initialExecution()
  {
    foreach (self::getCards(Locations::HAND, self::getLauncherId()) as $card) {
      self::transferToHand($card);
    }

    $colors = self::getUniqueColorsInLocation(Locations::HAND);
    if (count($colors) <= 2) {
      foreach (self::getCards(Locations::HAND) as $card) {
        self::transferToHand($card, self::getLauncherId());
      }
    } else {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(Arrays::encode($colors));
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $colors = Arrays::decode(self::getAuxiliaryValue());
    return self::youMust()->chooseTwoColors($colors);
  }

  public function handleTwoColorChoice(int $color1, int $color2)
  {
    self::notifyTwoColorChoice($color1, $color2);
    foreach (self::getCards(Locations::HAND) as $card) {
      if (in_array(self::getColor($card), [$color1, $color2])) {
        self::transferToHand($card, self::getLauncherId());
      }
    }
  }

}