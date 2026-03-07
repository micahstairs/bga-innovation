<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card65 extends AbstractCard
{

  // Evolution:
  //   - You may choose to either draw and score an [8] and then return a card from your score pile,
  //     or draw a card of value one higher than the highest card in your score pile.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->choose([0, 1]);
    } else {
      return self::youMust()->return()->fromYourScore();
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => [clienttranslate('Draw a ${age}'), 'age' => self::renderValue(self::getMaxValueInLocation(Locations::SCORE) + 1)],
      1 => [clienttranslate('Draw and score a ${age}'), 'age' => self::renderValue(8)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 0) {
      self::draw(self::getMaxValueInLocation(Locations::SCORE) + 1);
    } else {
      self::drawAndScore(8);
      self::setMaxSteps(2);
    }
  }

}