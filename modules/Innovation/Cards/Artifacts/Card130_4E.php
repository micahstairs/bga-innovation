<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card130_4E extends AbstractCard
{

  // Baghdad Battery (4th edition):
  //   - Meld a card from your hand. Score a card from your hand. If you do both, and the cards
  //     have different values, junk all cards in the decks of both values.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(-1);
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
        self::setAuxiliaryValue(self::getValue($card)); // Track value of melded card
        self::setMaxSteps(2);
    } else if (self::isSecondInteraction()) {
      $meldedValue = self::getAuxiliaryValue();
      $scoredValue = self::getValue($card);
      if ($meldedValue != -1 && $meldedValue != $scoredValue) {
        self::junkBaseDeck($meldedValue);
        self::junkBaseDeck($scoredValue);
      }
      
    }
  }

}