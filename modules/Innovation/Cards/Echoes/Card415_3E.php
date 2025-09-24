<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card415_3E extends AbstractCard
{

  // Calculator (3rd edition):
  //   - Score two bottom non-blue cards from your board. If you scored two and they have a total
  //     value less than 11, draw a card of that total value and repeat this dogma effect (once only).
  //   - You may splay your blue cards up.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        self::setAuxiliaryArray([]); // Tracks total value of cards scored
      }
      return self::youMust()->score()->exactly(2)->non(Colors::BLUE)->fromBottom()->fromYourBoard()->build();
    } else {
      return self::youMay()->splayUp(Colors::BLUE)->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray(self::getValue($card));
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $total = array_sum(self::getAuxiliaryArray());
      if ($total < 11) {
        self::draw($total);
        self::setAuxiliaryArray([]);
        self::setMaxSteps(2);
      }
    }
  }

}