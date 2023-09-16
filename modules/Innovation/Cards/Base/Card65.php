<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;

class Card65 extends Card
{

  // Evolution:
  //   - You may choose to either draw and score an [8] and then return a card from your score pile,
  //     or draw a card of value one higher than the highest card in your score pile.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [0, 1],
      ];
    } else {
      return [
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => [clienttranslate('Draw a ${age}'), 'age' => self::renderValue(self::getMaxValueInLocation('score') + 1)],
      1 => [clienttranslate('Draw and score a ${age}'), 'age' => self::renderValue(8)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 0) {
      self::draw(self::getMaxValueInLocation('score') + 1);
    } else {
      self::drawAndScore(8);
      self::setMaxSteps(2);
    }
  }

}