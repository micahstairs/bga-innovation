<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card456 extends AbstractCard
{
  // What Does The Fox Say
  //   - Draw two [11]s. Meld one of them, then meld the other and if it is your turn, super-execute
  //     it, otherwise self-execute it.

  public function initialExecution()
  {
    $card1 = self::draw(11);
    $card2 = self::draw(11);
    self::setAuxiliaryArray([$card1['id'], $card2['id']]);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'meld_keyword'                    => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::removeFromAuxiliaryArray($card['id']);
    $other_card_id = self::getAuxiliaryArray()[0];
    $other_card = self::meld(self::getCard($other_card_id));
    if (self::isTheirTurn()) {
      self::fullyExecute($other_card);
    } else {
      self::selfExecute($other_card);
    }
  }

}