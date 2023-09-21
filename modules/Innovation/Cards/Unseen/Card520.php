<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card520 extends AbstractCard
{

  // El Dorado:
  //   - Draw and meld a [3], a [2], and a [1]. If all three cards have a PROSPERITY, score all cards
  //     in the [5] deck. If two have a PROSPERITY, splay your green and blue cards right.

  public function initialExecution()
  {
    $numCardsWithProsperityIcons = 0;
    for ($i = 3; $i >= 1; $i--) {
      $card = self::drawAndMeld($i);
      if (self::hasIcon($card, Icons::PROSPERITY)) {
        $numCardsWithProsperityIcons++;
      }
    }
    if ($numCardsWithProsperityIcons === 3) {
      $cardsInDeck = self::getCardsKeyedByValue('deck');
      foreach ($cardsInDeck[5] as $card) {
        if ($card['type'] == CardTypes::BASE) {
          self::score($card);
        }
      }
    }
    if ($numCardsWithProsperityIcons >= 2) {
      self::splayRight(Colors::GREEN);
      self::splayRight(Colors::BLUE);
    }
  }
}
