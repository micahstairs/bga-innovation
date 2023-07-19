<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card353 extends Card
{

  // Pagoda
  // - 3rd edition:
  //   - Draw and reveal a [3]. If you have a card of matching color in your hand, tuck the card
  //     from your hand and meld the drawn card. Otherwise, foreshadow the drawn card.
  // - 4th edition:
  //   - Draw and foreshadow a [3]. You may tuck another card of matching color from your hand. If
  //     you do, meld the drawn card. If you do, and Pagoda was foreseen, meld all cards of that
  //     color from all other boards.

  public function initialExecution()
  {
    $card = self::isFirstOrThirdEdition() ? self::drawAndReveal(3) : self::drawAndForeshadow(3);
    self::setAuxiliaryValue($card['color']);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep()) {
      return [
        'can_pass'      => self::isFourthEdition(),
        'location_from' => 'hand',
        'tuck_keyword'  => true,
        'color'         => [self::getAuxiliaryValue()],
      ];
    } else {
      return [
        'n'             => 'all',
        'owner_from'    => 'any other player',
        'location_from' => 'pile',
        'color'         => [self::getAuxiliaryValue()],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      // Reveal hand to prove that there were no matching cards of the drawn card's color.
      self::revealHand();
      if (self::isFirstOrThirdEdition()) {
        self::foreshadow(self::getRevealedCard());
      }
    } else {
      self::meld(self::getRevealedCard());
      if (self::isFourthEdition() && self::wasForeseen()) {
        self::setMaxSteps(2);
      }
    }
  }

}