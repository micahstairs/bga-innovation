<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card506 extends AbstractCard
{

  // Secret Secretorum:
  //   - Return five cards from your hand and/or score pile. Draw two cards of value equal to the
  //     number of different colors of cards you return. Meld one of the drawn cards and score the
  //     other.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      $this->game->setAuxiliaryValueFromArray([]);
      return [
        'location_from' => Locations::HAND_OR_SCORE,
        'location_to'   => 'revealed,deck',
        'n'             => 5,
      ];
    } else {
      return [
        'location_from'                   => 'hand',
        'meld_keyword'                    => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      // Keep track of the colors of the cards being returned
      $colors = $this->game->getAuxiliaryValueAsArray();
      $colors[] = $card['color'];
      $this->game->setAuxiliaryValueFromArray(array_unique($colors));
    } else {
      // Score the other card
      $cardIds = self::getAuxiliaryArray();
      $cardIdToScore = $card['id'] == $cardIds[0] ? $cardIds[1] : $cardIds[0];
      self::score(self::getCard($cardIdToScore));
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      // Draw two cards and store the IDs in the auxiliary array
      $numColors = count($this->game->getAuxiliaryValueAsArray());
      $card1 = self::draw($numColors);
      $card2 = self::draw($numColors);
      self::setAuxiliaryArray([$card1['id'], $card2['id']]);
    }
  }
}