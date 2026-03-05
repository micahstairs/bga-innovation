<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card559 extends AbstractCard
{

  // Handbag:
  //   - You may choose to either transfer your bottom card of each color to your hand, or tuck all
  //     cards from your score pile, or choose a value and score all cards from your hand of that value.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choose([1, 2, 3]);
    } else {
      if (self::getAuxiliaryValue() === 2) {
        return self::youMust()->tuck()->all()->fromYourScore();
      } else {
        return self::youMust()->chooseValue();
      }
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Transfer your bottom cards to your hand'),
      2 => clienttranslate('Tuck all cards from your score pile'),
      3 => clienttranslate('Score all cards from your hand of a specific value'),
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      foreach (Colors::ALL as $color) {
        self::transferToHand(self::getBottomCardOfColor($color));
      }
    } else {
      self::setAuxiliaryValue($choice);
      self::setMaxSteps(2);
    }
  }

  public function handleValueChoice(int $value)
  {
    self::notifyValueChoice($value);
    foreach (self::getCardsKeyedByValue('hand')[$value] as $card) {
      self::score($card);
    }
  }

}