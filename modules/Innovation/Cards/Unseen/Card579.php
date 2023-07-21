<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

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
    if (self::getEffectNumber() === 1) {
      $this->game->setAuxiliaryArray([]);
      return [
        'n' => 'all',
        'location_from' => 'score',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::RED],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    $values = $this->game->getAuxiliaryArray();
    $returnedValue = $card['age'];
    if (!in_array($returnedValue, $values)) {
      return $this->game->setAuxiliaryArray(array_merge($values, [$returnedValue]));
    }
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() === 1) {
      $values = $this->game->getAuxiliaryArray();
      for ($i = 0; $i < count($values); $i++) {
        self::drawAndScore(10);
      }
    }
  }

}