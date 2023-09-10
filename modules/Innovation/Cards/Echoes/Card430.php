<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;

class Card430 extends Card
{

  // Flash Drive (3rd edition)
  //   - I DEMAND you return four cards from your score pile!
  //   - Return a card from your score pile. If you do, you may splay any one color of your cards up.
  // Barcode (4th edition)
  //   - I DEMAND you return a card of matching value from your score pile for each bonus on your board!
  //   - You may splay any one color of your cards up. If Barcode was foreseen, splay all your colors up.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      self::setMaxSteps(1);
    } else {
      if (self::isDemand()) {
        self::setActionScopedAuxiliaryArray(self::getBonuses()); // Track values left to remove from score pile
      } else if (self::wasForeseen()) {
        for ($color = 0; $color < 5; $color++) {
          self::splayUp($color);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition()) {
        return [
          'n'              => 4,
          'location_from'  => 'score',
          'return_keyword' => true,
        ];
      } else {
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
      }
    } else if (self::isFirstOrThirdEdition() && self::isFirstInteraction()) {
      return [
        'location_from'  => 'score',
        'return_keyword' => true,
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
    if (self::isDemand() && self::isFourthEdition()) {
      $remainingValues = self::removeFromActionScopedAuxiliaryArray($card['age']);
      if (count($remainingValues) > 0) {
        self::setNextStep(1);
      }
    } else if (self::isFirstOrThirdEdition() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}