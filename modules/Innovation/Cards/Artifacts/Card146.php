<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card146 extends AbstractCard
{

  // Delft Pocket Telescope
  // - 3rd edition:
  //   - Return a card from your score pile. If you do, draw a [5] and a [6], then reveal one of
  //     the drawn cards that has an icon in common with the returned card. If you cannot, return
  //     the drawn cards and repeat this effect.
  // - 4th edition:
  //   - Return a card from your score pile. If you do, draw a [5] and a [6], then reveal one of
  //     the drawn cards that has a symbol in common with the returned card. If you cannot, return
  //     the drawn cards and repeat this effect.


  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'score',
        'location_to'   => 'revealed,deck',
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'n'                               => 2,
        'location_from'                   => 'revealed',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'location_from'                   => 'hand',
        'location_to'                     => 'revealed',
        'card_ids_are_in_auxiliary_array' => true,
        'enable_autoselection'            => false, // Automating this always reveals hidden info
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $card1 = self::draw(5);
      $card2 = self::draw(6);

      $card1Matches = self::hasIconInCommon($card, $card1);
      $card2Matches = self::hasIconInCommon($card, $card2);

      if (!$card1Matches && !$card2Matches) {
        self::reveal($card1);
        self::reveal($card2);
        self::notifyAll(clienttranslate('Neither card has a icon in common with the returned card.'));
        self::setAuxiliaryArray([$card1['id'], $card2['id']]);
        self::setMaxSteps(2);
      } else {
        self::setMaxSteps(3);
        self::setNextStep(3);
        $cardIds = [];
        if ($card1Matches) {
          $cardIds[] = $card1['id'];
        }
        if ($card2Matches) {
          $cardIds[] = $card2['id'];
        }
        self::setAuxiliaryArray($cardIds);
      }
    } else if (self::isSecondInteraction()) {
      self::setNextStep(1);
      self::setMaxSteps(1);
    } else {
      self::transferToHand($card);
    }
  }
}