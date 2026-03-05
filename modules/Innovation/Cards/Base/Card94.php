<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card94 extends AbstractCard
{
  // Specialization:
  // - 3rd edition:
  //   - Reveal a card from your hand. Take into your hand the top card of that color from all opponents' boards.
  //   - You may splay your yellow or blue cards up.
  // - 4th edition:
  //   - Reveal a card from your hand. Transfer to your hand the top card of that color from all opponents' boards.
  //   - You may splay your yellow or blue cards up.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->reveal()->fromYourHand();
    } else {
      return self::youMay()->splayUp([Colors::BLUE, Colors::YELLOW]);
    }
  }

  public function handleCardChoice(array $card)
  {
    $color = self::getColor($card);
    foreach (self::getOpponentIds() as $opponentId) {
      self::transferToHand(self::getTopCardOfColor($color, $opponentId));
    }
    self::transferToHand($card);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::canSplay([Colors::BLUE, Colors::YELLOW]);
  }

}