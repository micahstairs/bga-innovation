<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card417 extends Card
{

  // Helicopter
  // - 3rd edition
  //   - Transfer a top card other than Helicopter from any player's board to its owner's score
  //     pile. You may return a card from your hand which shares an icon with the transferred
  //     card. If you do, repeat this dogma effect.
  // - 4th edition
  //   - Transfer a top card other than Helicopter from any player's board to its owner's score
  //     pile. You may return a card from your hand which shares an icon with the transferred
  //     card. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'owner_from'    => 'any player',
        'location_from' => 'board',
        'location_to'   => 'none',
        'not_id'        => 417, // Helicopter
      ];
    } else {
      return [
        'can_pass'                        => true,
        'location_from'                   => 'hand',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
        'enable_autoselection'            => false,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::transferToScorePile($card, $card['owner']);
      $cardIds = [];
      foreach (self::getCards('hand') as $cardInHand) {
        if (self::hasIconInCommon($cardInHand, $card)) {
          $cardIds[] = $cardInHand['id'];
        }
      }
      self::setAuxiliaryArray($cardIds);
    } else {
      self::setNextStep(1);
    }
  }

}