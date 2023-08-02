<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card390 extends Card
{

  // Steamboat
  // - 3rd edition
  //   - I DEMAND you draw and reveal a [6]! If it is blue or yellow, transfer it and all cards in
  //     your hand to my hand! If it is red or green, keep it and transfer two cards from your
  //     score pile to mine! If it is purple, keep it!
  // - 4th edition
  //   - I DEMAND you draw and reveal a [6]! If it is blue or yellow, transfer it and all cards in
  //     your hand to my hand! If it is red or green, transfer two cards from your score pile to
  //     mine!

  public function initialExecution()
  {
    $card = self::drawAndReveal(6);
    if (self::isBlue($card) || self::isYellow($card)) {
      self::transferToHand($card, self::getLauncherId());
      foreach (self::getCards('hand') as $cardInHand) {
        self::transferToHand($cardInHand, self::getLauncherId());
      }
    } else if (self::isRed($card) || self::isGreen($card)) {
      self::putInHand($card);
      self::setMaxSteps(1);
    } else {
      self::putInHand($card);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'             => 2,
      'location_from' => 'score',
      'location_to'   => 'score',
    ];
  }

}