<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

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
    self::setAuxiliaryArray(self::getUniqueValues('score'));
    return [
      'owner_from'                      => 0,
      'location_from'                   => 'achievements',
      'location_to'                     => 'junk',
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

}