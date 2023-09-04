<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card126 extends Card
{

  // Rosetta Stone
  //   - Choose a card type. Draw two [2] of that type. Meld one and transfer the other to an
  //     opponent's board.

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_type' => true];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from'                   => 'hand',
        'meld_keyword'                    => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'choose_player' => true,
        'players'       => $this->game->getActiveOpponents(self::getPlayerId()),
      ];
    }
  }

  public function handleSpecialChoice(int $choice)
  {
    if (self::isFirstInteraction()) {
      $card1 = self::drawType(2, $choice);
      $card2 = self::drawType(2, $choice);
      self::setAuxiliaryArray([$card1['id'], $card2['id']]);
    } else {
      self::transferToBoard(self::getCard(self::getAuxiliaryArray()[0]), $choice);
    }
  }

  public function handleCardChoice(array $card)
  {
    self::removeFromAuxiliaryArray($card['id']);
  }

}