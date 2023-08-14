<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Utils\Arrays;

class Card405 extends Card
{

  // Radio Telescope
  // - 3rd edition
  //   - For every two [CONCEPT] on your board, draw a [9]. Meld one of the cards drawn and return
  //     the rest. If you meld AI due to this dogma effect, you win.
  // - 4th edition
  //   - For every two [CONCEPT] on your board, draw a [9], and if Radio Telescope was foreseen,
  //     draw a [10]. Meld one of the cards you draw and return the rest. If you meld A. I. due to
  //     this effect, you win.

  public function initialExecution()
  {
    $numCardsToDraw = $this->game->intDivision(self::getIconCount($this->game::CONCEPT), 2);
    if ($numCardsToDraw > 0 || self::wasForeseen()) {
      $cardIds = [];
      for ($i = 0; $i < $numCardsToDraw; $i++) {
          $card = self::draw(9);
          $cardIds[] = $card['id'];
      }
      if (self::wasForeseen()) {
          $card = self::draw(10);
          $cardIds[] = $card['id'];
      }
      self::setAuxiliaryArray($cardIds);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'meld_keyword' => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n' => count(self::getAuxiliaryArray()),
        'location_from' => 'hand',
        'return_keyword' => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isFirstInteraction()) {
      if ($card['id'] == 103) { // 'A. I.'
        self::win();
      } else {
        $remainingIds = Arrays::removeElement(self::getAuxiliaryArray(), $card['id']);
        self::setAuxiliaryArray($remainingIds);
      }
    }
  }

}