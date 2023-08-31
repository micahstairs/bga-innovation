<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card116 extends Card
{

  // Priest-King
  // - 3rd edition:
  //   - Score a card from your hand. If you have a top card matching its color, execute each of
  //     the top card's non-demand dogma effects. Do not share them.
  //   - Claim an achievement, if eligible.
  // - 4th edition:
  //   - Score a card from your hand. If you have a top card matching its color, fully execute it
  //     if it is your turn, otherwise self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'location_from' => 'hand',
        'location_to' => 'revealed,score',
        'score_keyword' => true,
      ];
    } else {
      return ['achieve_if_eligible' => true];
    }
  }

  public function handleCardChoice(array $card)
  {
    $topCard = self::getTopCardOfColor($card['color']);
    if (self::isFourthEdition() && self::isTheirTurn()) {
      self::fullyExecute($topCard);
    } else {
      self::selfExecute($topCard);
    }
  }

}