<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card372_4E extends AbstractCard
{

  // Pencil (4th edition):
  //   - ECHO: Draw a [5].
  //   - You may return up to three cards from your hand. If you do, draw that many cards of value
  //     one higher than the highest card you return. Return all but one of them, and foreshadow
  //     the remaining card.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(5);
    } else if (self::isFirstNonDemand()) {
      self::setAuxiliaryValue(0); // Track the value of the highest returned card
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'n_min'          => 1,
        'n_max'          => 3,
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    } else {
      return [
        'n'                               => count(self::getAuxiliaryArray()) - 1,
        'location_from'                   => 'hand',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(max(self::getAuxiliaryValue(), $card['age']));
    } else if (self::isSecondInteraction()) {
      self::removeFromAuxiliaryArray($card['id']);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() > 0) {
      $valueToDraw = self::getAuxiliaryValue() + 1;
      $cardIds = [];
      for ($i = 0; $i < self::getNumChosen(); $i++) {
        $card = self::draw($valueToDraw);
        $cardIds[] = $card['id'];
      }
      self::setAuxiliaryArray($cardIds); // Track cards to foreshadow/return
      self::setMaxSteps(2);
    } else if (self::isSecondInteraction()) {
      // NOTE: There should always be exactly one card in the array at this point
      foreach (self::getAuxiliaryArray() as $cardId) {
        self::foreshadow(self::getCard($cardId));
      }
    }
  }

}