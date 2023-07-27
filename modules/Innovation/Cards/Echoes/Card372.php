<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card372 extends Card
{

  // Pencil
  // - 3rd edition:
  //   - ECHO: Draw a [5].
  //   - You may return up to three cards from your hand. If you do, draw that many cards of value
  //     one higher than the highest card you returned. Foreshadow one of them, and return the rest
  //     of the drawn cards.
  // - 4th edition:
  //   - ECHO: Draw a [5].
  //   - You may return up to three cards from your hand. If you do, draw that many cards of value
  //     one higher than the highest card you return. Foreshadow one of them, and return the rest
  //     of the drawn cards.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(5);
    } else {
      self::setAuxiliaryValue(0); // Track the value of the highest returned card
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'can_pass'       => true,
        'n_min'          => 1,
        'n_max'          => 3,
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else if (self::getCurrentStep() === 2) {
      return [
        'location_from'                   => 'hand',
        'foreshadow_keyword'              => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'                               => 'all',
        'location_from'                   => 'hand',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::getCurrentStep() === 1) {
      self::setAuxiliaryValue(max(self::getAuxiliaryValue(), $card['age']));
    } else if (self::getCurrentStep() === 2) {
      self::setAuxiliaryArray(array_diff(self::getAuxiliaryArray(), [$card['id']]));
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() === 1 && self::getNumChosen() > 0) {
      $valueToDraw = self::getAuxiliaryValue() + 1;
      $cardIds = [];
      for ($i = 1; $i <= self::getNumChosen(); $i++) {
        $card = self::draw($valueToDraw);
        $cardIds[] = $card['id'];
      }
      self::setAuxiliaryArray($cardIds); // Track cards to foreshadow/return
      self::setMaxSteps(3);
    }
  }

}