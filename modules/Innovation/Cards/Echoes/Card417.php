<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;

class Card417 extends Card
{

  // Helicopter
  // - 3rd edition
  //   - Transfer a top card other than Helicopter from any player's board to its owner's score
  //     pile. You may return a card from your hand which shares an icon with the transferred
  //     card. If you do, repeat this dogma effect.
  // - 4th edition
  //   - Transfer a top card other than Helicopter from any player's board to its owner's score
  //     pile. You may return a card from your hand which shares an icon type with the transferred
  //     card. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'owner_from'  => 'any player',
        'choose_from' => 'board',
        'not_id'      => CardIds::HELICOPTER,
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