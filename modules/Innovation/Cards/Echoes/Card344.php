<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card344 extends Card
{

  // Puppet
  // - 3rd edition:
  //   - No effect.
  // - 4th edition:
  //   - Junk an available achievement of value equal to the value of a card in your score pile.

  public function initialExecution()
  {
    if (self::isFourthEdition()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $values = self::getUniqueValues('score');
    $cardIds = [];
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card)  {
      if (self::isValuedCard($card) && in_array(intval($card['age']), $values)) {
        $cardIds[] = $card['id'];
      }
    }
    self::setAuxiliaryArray($cardIds);
    return [
      'location_from'                   => Locations::AVAILABLE_ACHIEVEMENTS,
      'junk_keyword'                    => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

}