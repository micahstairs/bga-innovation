<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card116 extends AbstractCard
{

  // Priest-King
  // - 3rd edition:
  //   - Score a card from your hand. If you have a top card matching its color, execute each of
  //     the top card's non-demand dogma effects. Do not share them.
  //   - Claim an achievement, if eligible.
  // - 4th edition:
  //   - Score a card from your hand. If you have a top card matching its color, super-execute
  //     that top card it is your turn, otherwise self-execute it.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'location_from' => Locations::HAND,
        'location_to'   => Locations::REVEALED_THEN_SCORE,
        'score_keyword' => true,
      ];
    } else {
      return ['achieve_if_eligible' => true];
    }
  }

  public function handleCardChoice(array $card)
  {
    $topCard = self::getTopCardOfColor(self::getColor($card));
    if (self::isFourthEdition() && self::isTheirTurn()) {
      self::superExecute($topCard);
    } else {
      self::selfExecute($topCard);
    }
  }

}