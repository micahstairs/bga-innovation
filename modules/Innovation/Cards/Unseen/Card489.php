<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

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

    $colors = self::getUniqueColors(Locations::HAND);
    if (count($colors) <= 2) {
      foreach (self::getCards(Locations::HAND) as $card) {
        self::transferToHand($card, self::getLauncherId());
      }
    } else {
        self::setMaxSteps(1);
        $this->game->setAuxiliaryValueFromArray($colors);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_two_colors' => true,
        'color'             => $this->game->getAuxiliaryValueAsArray(),
    ];
  }

  public function handleTwoColorChoice(int $color1, int $color2) {
    self::notifyTwoColorChoice($color1, $color2);
    foreach (self::getCards(Locations::HAND) as $card) {
      if (in_array($card['color'], [$color1, $color2])) {
        self::transferToHand($card, self::getLauncherId());
      }
    }
  }

}