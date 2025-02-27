<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card59 extends AbstractCard
{
  // - Classification:
  // - 3rd edition:
  //   - Reveal the color of a card in your hand. Take into your hand all cards of that color from
  //     all other player's hands. Then meld all cards of that color from your hand.
  // - 4th edition:
  //   - Reveal a card from your hand. Take into your hand all cards of that color from all opponents'
  //     hands. Then, meld all cards of that color from your hand.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->reveal()->fromYourHand()->build();
    } else {
      return self::youMay()->meld()->all()->withColor(self::getAuxiliaryValue())->fromYourHand()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::transferToHand($card);
    $color = self::getColor($card);
    self::setAuxiliaryValue($color); // Track the chosen color

    self::revealHand();
    foreach (self::getOtherPlayerIds() as $otherPlayerId) {
      self::revealHand($otherPlayerId);
      foreach (self::getCards(Locations::HAND, $otherPlayerId) as $otherCard) {
        if (self::getColor($card) == $color) {
          self::transferToHand($otherCard);
        }
      }
    }

    self::setMaxSteps(2);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}