<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card181 extends AbstractCard
{
  // Colt Paterson Revolver
  //   - I COMPEL you to reveal your hand! Draw a [7]! If the color of the drawn card matches the
  //     color of any other card in your hand, return all cards in your hand and all cards in your
  //     score pile!

  public function initialExecution()
  {
    self::revealHand();

    // If the player has other cards in hand, we need to reveal the card first in order to prove to other players
    // whether the card matched the color of another card in hand.
    if (count(self::getCards(Locations::HAND)) > 0) {
      $drawnCard = self::transferToHand(self::drawAndReveal(7));
    } else {
      $drawnCard = self::draw(7);
    }

    $matches = false;
    foreach (self::getCards(Locations::HAND) as $card) {
      if ($card['id'] != $drawnCard['id'] && $card['color'] == $drawnCard['color']) {
        $matches = true;
        break;
      }
    }

    if ($matches) {
      self::notifyAll(clienttranslate("The drawn card's color matches the color of another card in hand."));
      self::setMaxSteps(1);
    } else {
      self::notifyAll(clienttranslate("The drawn card's color does not match the color of another card in hand."));
    }

  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => Locations::HAND_OR_SCORE,
      'return_keyword' => true,
    ];
  }

}