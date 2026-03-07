<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Directions;

class Card555 extends AbstractCard
{

  // Blacklight:
  //   - Choose to either unsplay one color of your cards, or splay up an unsplayed color on your
  //     board and draw a [9].

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choose([1, 2]);
    } else {
      if (self::getAuxiliaryValue() === 1) {
        return self::youMust()->unsplay();
      } else {
        return self::youMust()->splayUp()->currentlyUnsplayed();
      }
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Unsplay one color'),
      2 => [clienttranslate('Splay up and draw a ${age}'), 'age' => self::renderValue(9)],
    ]);
  }

  public function handleListChoice($choice)
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