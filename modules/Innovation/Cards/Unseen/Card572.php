<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card572 extends Card
{

  // Surveillance:
  //   - I DEMAND you reveal your hand! If the colors of cards in your hand match the colors of
  //     cards in my hand, and you have a card in your hand, I win!
  //   - Draw a [10].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::revealHand(self::getPlayerId());
      self::revealHand(self::getLauncherId());
      $playerColors = self::getUniqueColors('hand', self::getPlayerId());
      $launcherColors = self::getUniqueColors('hand', self::getLauncherId());
      if (count($playerColors) > 0 && self::isUnorderedEqual($playerColors, $launcherColors)) {
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

  private function isUnorderedEqual(array $a, array $b): bool
  {
    return count(array_intersect($a, $b)) === count($a);
  }

}