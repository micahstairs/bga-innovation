<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;

class Card518 extends Card
{

  // Spanish Inquisition:
  //   - I DEMAND you return all but the highest cards from your hand and all but the highest cards
  //     from your score pile!
  //   - If Spanish Inquisition is a top card on your board, return all red cards from your board.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $cardIds = [];
      $maxValueInHand = self::getMaxValueInLocation('hand');
      foreach (self::getCards('hand') as $card) {
        if ($card['age'] < $maxValueInHand) {
          $cardIds[] = $card['id'];
        }
      }
      $maxValueInScore = self::getMaxValueInLocation('score');
      foreach (self::getCards('score') as $card) {
        if ($card['age'] < $maxValueInScore) {
          $cardIds[] = $card['id'];
        }
      }
      if (count($cardIds) > 0) {
        self::setMaxSteps(2);
        self::setAuxiliaryArray($cardIds);
      }
    } else {
      $topCard = self::getTopCardOfColor(Colors::RED);
      if ($topCard && $topCard['id'] == CardIds::SPANISH_INQUISITION) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'n'                               => 'all',
        'location_from'                   => 'hand,score',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'pile',
        'return_keyword' => true,
        'color'          => [Colors::RED],
      ];
    }
  }

}