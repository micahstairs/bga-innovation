<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card430_3E extends AbstractCard
{

  // Barcode (4th edition):
  //   - I DEMAND you return a card of matching value from your score pile for each bonus on your board!
  //   - You may splay any one color of your cards up. If Barcode was foreseen, splay all your colors up.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setActionScopedAuxiliaryArray(self::getBonuses()); // Track values left to remove from score pile
    } else if (self::wasForeseen()) {
      foreach (Colors::ALL as $color) {
        self::splayUp($color);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      $values = self::getActionScopedAuxiliaryArray();
      $cardIds = [];
      foreach (self::getCards('score') as $card) {
        if (in_array($card['age'], $values)) {
          $cardIds[] = $card['id'];
        }
      }
      self::setAuxiliaryArray($cardIds);
      return [
        'location_from'                   => 'score',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
      ];
    }
  }

  function handleCardChoice(array $card)
  {
    if (self::isDemand()) {
      $remainingValues = self::removeFromActionScopedAuxiliaryArray($card['age']);
      if (count($remainingValues) > 0) {
        self::setNextStep(1);
      }
    }
  }

}