<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card344 extends AbstractCard
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

  public function getInteractionOptions(): InteractionBuilder
  {
    $values = self::getUniqueValuesInLocation(Locations::SCORE);
    $cardIds = [];
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
      if (self::isValuedCard($card) && in_array(intval(self::getValue($card)), $values)) {
        $cardIds[] = self::getId($card);
      }
    }
    self::setAuxiliaryArray($cardIds);
    return self::youMust()->junk()->onlyCardsInAuxiliaryArray()->fromAvailableAchievements();
  }

}