<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card527 extends AbstractCard
{
  // Cabal
  //   - I DEMAND you transfer all cards from your hand that have a value matching any of my
  //     secrets to my score pile! Draw a [5]!
  //   - Safeguard an available achievement of value equal to a top card on your board.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $secretValues = self::getValues(self::getCards(Locations::SAFE, self::getLauncherId()));
      foreach (self::getCards(Locations::HAND) as $card) {
        if (in_array(self::getValue($card), $secretValues)) {
          self::transferToScorePile($card, self::getLauncherId());
        }
      }
      self::draw(5);
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $cardIds = [];
    $values = self::getValues(self::getTopCards());
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $achievement) {
      if (self::isValuedCard($achievement) && (in_array(self::getValue($achievement), $values))) {
        $cardIds[] = $achievement['id'];
      }
    }
    self::setAuxiliaryArray($cardIds);
    return [
      'safeguard_keyword'               => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

}