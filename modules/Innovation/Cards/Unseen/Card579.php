<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card579 extends Card
{

  // Cryptocurrency:
  //   - Return all cards from your score pile. For each different value of card you return, draw
  //     and score a [10].
  //   - You may splay your red cards up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      self::setAuxiliaryArray([]);
      return [
        'n'              => 'all',
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [Colors::RED],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray($card['age']);
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $values = array_unique(self::getAuxiliaryArray());
      for ($i = 0; $i < count($values); $i++) {
        self::drawAndScore(10);
      }
    }
  }

}