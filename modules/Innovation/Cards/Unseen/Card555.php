<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card555 extends Card
{

  // Blacklight:
  //   - Choose to either unsplay one color of your cards, or splay up an unsplayed color on your
  //     board and draw a [9].

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choices' => [1, 2]];
    } else {
      if (self::getAuxiliaryValue() === 1) {
         return [
          'splay_direction' => $this->game::UNSPLAYED,
         ];
      } else {
        return [
          'splay_direction' => $this->game::UP,
          'has_splay_direction' => [$this->game::UNSPLAYED],
        ];
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      1 => clienttranslate('Unsplay one color'),
      2 => [clienttranslate('Splay up and draw a ${age}'), 'age' => $this->game->getAgeSquare(9)],
    ]);
  }

  public function handleSpecialChoice($choice)
  {
    self::setAuxiliaryValue($choice);
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction() && self::getAuxiliaryValue() === 2) {
      self::draw(9);
    }
  }

}