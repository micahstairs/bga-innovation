<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card482 extends Card
{

  // Proverb:
  //   - Draw, reveal, and return a [1]. If the color of the returned card is yellow or purple,
  //     safeguard an available achievement of value equal to a card in your hand, then return all
  //     cards from your hand. Otherwise, draw two [1].

  public function initialExecution()
  {
    $card = self::drawAndReveal(1);
    self::return($card);
    if (self::isYellow($card) || self::isPurple($card)) {
      $cardIds = [];
      $countsByValue = self::countCardsKeyedByValue(Locations::HAND);
      foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
        if (self::isValuedCard($card)) {
          if ($countsByValue[$card['age']] > 0) {
            $cardIds[] = $card['id'];
          }
        }
      }
      self::setAuxiliaryArray($cardIds);
      self::setMaxSteps(2);
    } else {
      self::draw(1);
      self::draw(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'safeguard_keyword'               => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    }
  }

}