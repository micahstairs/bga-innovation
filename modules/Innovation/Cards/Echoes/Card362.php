<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card362 extends Card
{

  // Sandpaper
  // - 3rd edition:
  //   - You may return any number of cards from your hand. Draw that many [3], and then meld a card from your hand.
  // - 4th edition:
  //   - You may return any number of cards from your hand. Draw a [3] for each card you return.
  //   - Meld a card from your hand.
  //   - If Sandpaper was foreseen, foreshadow all cards in your hand.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      self::setMaxSteps(2);
    } else if (self::getEffectNumber() <= 2 || (self::getEffectNumber() === 3 && self::wasForeseen())) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1 && self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'n_min'          => 1,
        'n_max'          => 'all',
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else if (self::isFirstOrThirdEdition() || self::getEffectNumber() === 2) {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'n'                  => 'all',
        'location_from'      => 'hand',
        'foreshadow_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() === 1 && self::isFirstInteraction()) {
      for ($i = 0; $i < self::getNumChosen(); $i++) {
        self::draw(3);
      }
    }
  }

}