<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card174 extends Card
{
  // Marcha Real
  //   - Reveal and return two cards from your hand. If they have the same value, draw a card of
  //     value one higher. If they have the same color, claim an achievement, ignoring eligibility.

  public function initialExecution()
  {
    self::setAuxiliaryValue([]);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n'             => 2,
        'location_from' => Locations::HAND,
        'location_to'   => Locations::REVEALED_THEN_DECK,
      ];
    } else {
      return ['achieve_keyword' => true];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::addToAuxiliaryArray($card['id']);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $cardIds = self::getAuxiliaryArray();
      if (count($cardIds) === 0) {
        // If none are returned, they are still considered to have the same value
        self::draw(0);
      } else if (count($cardIds) === 2) {
        $card1 = self::getCard($cardIds[0]);
        $card2 = self::getCard($cardIds[1]);

        if ($card1['age'] == $card2['age']) {
          self::notifyAll(clienttranslate('The cards both have the same value.'));
          self::draw($card1['age'] + 1);
        } else {
          self::notifyAll(clienttranslate('The cards do not have the same value.'));
        }
        if ($card1['color'] == $card2['color']) {
          self::notifyAll(clienttranslate('The cards both have the same color.'));
          self::setMaxSteps(2);
        } else {
          self::notifyAll(clienttranslate('The cards do not have the same color.'));
        }
      }
    }
  }

}