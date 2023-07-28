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
    self::setAuxiliaryValue($card['id']); // Track the drawn card
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $color = self::getCard(self::getAuxiliaryValue())['color'];
    if (self::getCurrentStep() === 1) {
      return [
        'can_pass'      => self::isFourthEdition(),
        'location_from' => 'hand',
        'tuck_keyword'  => true,
        'color'         => [$color],
      ];
    } else {
      return [
        'n'             => 'all',
        'owner_from'    => 'any other player',
        'location_from' => 'pile',
        'color'         => [$color],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      if (self::isFirstOrThirdEdition()) {
        // Reveal hand to prove that there were no matching cards of the drawn card's color.
        self::revealHand();
        self::foreshadow(self::getRevealedCard());
      }
    } else {
      // Only reveal (in 4th edition) if a card was actually tucked
      if (self::isFourthEdition()) {
        // TODO(LATER): It would be a bit more natural if this was revealed before the card was
        // actually tucked (but after the player decided to tuck a card).
        $this->game->revealCardWithoutMoving(self::getPlayerId(), self::getCard(self::getAuxiliaryValue()));
      }
      self::meld(self::getRevealedCard());
      if (self::wasForeseen()) {
        self::setMaxSteps(2);
      }
    }
  }

}