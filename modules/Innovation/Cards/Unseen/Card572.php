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
      // Check each active opponent (we technically only need to check opponents which will be executing the non-demand, but doing them all is simpler)
      $colors = self::getUniqueColorsInHand(self::getPlayerId());
      foreach ($this->game->getActiveOpponentIds(self::getPlayerId()) as $opponentId) {
        $opponentColors = self::getUniqueColorsInHand($opponentId);
        if (count($colors) === count($opponentColors) && !array_diff($colors, $opponentColors) && !array_diff($opponentColors, $colors)) {
          $this->game->setIndexedAuxiliaryValue($opponentId, 1);
        }
      }
    } else if (self::getEffectNumber() === 1) {
      if (count($this->game->getCardsInHand(self::getPlayerId())) > 0 && $this->game->getIndexedAuxiliaryValue(self::getPlayerId())) {
        self::win();
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