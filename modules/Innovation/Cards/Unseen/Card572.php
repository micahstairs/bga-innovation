<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card572 extends Card
{

  // Surveillance:
  //   - I demand you reveal your hand!
  //   - If the colors of cards in your hand matches the colors of revealed cards in an opponent's
  //     hand, and you have a card in your hand, you win.
  //   - Draw a [10].

  public function initialExecution()
  {
    if (self::isDemand()) {
      $this->game->revealHand(self::getPlayerId());
      // Check each opponent affected by this demand (the others won't have their flag set)
      $colors = self::getUniqueColorsInHand(self::getLauncherId());
      $opponentColors = self::getUniqueColorsInHand(self::getPlayerId());
                
      if (count(array_diff($colors, $opponentColors)) == 0 && count(array_diff($opponentColors, $colors)) == 0) {
        $this->game->setIndexedAuxiliaryValue(self::getPlayerId(), 1);
      }
       
    } else if (self::getEffectNumber() === 1) {
      foreach ($this->game->getActiveOpponentIds(self::getPlayerId()) as $opponentId) {
        if ($this->game->countCardsInHand(self::getPlayerId()) > 0 && $this->game->getIndexedAuxiliaryValue($opponentId) == 1) {
          $this->game->revealHand(self::getPlayerId()); // reveal hand to prove winning colors
          self::win();
        }
      }
    } else {
      self::draw(10);
    }
  }

  private function getUniqueColorsInHand(int $playerId): array
  {
    $colors = [];
    foreach ($this->game->getCardsInHand($playerId) as $card) {
      if (!in_array($card['color'], $colors)) {
        $colors[] = $card['color'];
      }
    }
    return $colors;
  }

}