<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

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
      $countsByAge = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'hand');
      foreach (self::getCards('achievements', 0) as $card) {
        if (self::isValuedCard($card)) {
          if ($countsByAge[$card['age']] > 0) {
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
        'owner_from'                      => 0,
        'location_from'                   => 'achievements',
        'location_to'                     => 'safe',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => 'hand',
        'location_to'   => 'deck',
      ];
    }
  }

}