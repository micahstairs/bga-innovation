<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card196 extends AbstractCard
{
  // Luna 3
  // - 3rd edition:
  //   - Return all cards from your score pile. Draw and score a card of value equal to the number
  //     of cards you return.
  // - 4th edition:
  //   - Return all cards from your score pile. Draw and score a card of value equal to the number
  //     of cards you return.
  //   - Choose a value. Junk all cards in that deck.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    } else {
      return ['choose_value' => true];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(self::getNumChosen());
    }
  }

  public function handleValueChoice(int $value)
  {
    self::junkBaseDeck($value);
  }

}