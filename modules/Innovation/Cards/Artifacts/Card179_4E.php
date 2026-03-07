<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card179_4E extends AbstractCard
{
  // International Prototype Metre Bar (4th edition):
  //   - Choose a value. Draw and reveal three cards of that value. Splay up the colors of the
  //     cards. If the number of cards of each of those colors on your board is equal to that
  //     value, you win. Otherwise, return the drawn cards.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue();
    } else {
      return self::youMust()->return()->all()->fromYourRevealed();
    }
  }

  public function handleValueChoice(int $value)
  {
    self::notifyValueChoice($value);

    $card1 = self::drawAndReveal($value);
    $card2 = self::drawAndReveal($value);
    $card3 = self::drawAndReveal($value);

    self::splayUp(self::getColor($card1));
    self::splayUp(self::getColor($card2));
    self::splayUp(self::getColor($card3));

    $count1 = self::countVisibleCardsInStack(self::getColor($card1));
    $count2 = self::countVisibleCardsInStack(self::getColor($card2));
    $count3 = self::countVisibleCardsInStack(self::getColor($card3));

    if ($count1 === $value && $count2 === $value && $count3 === $value) {
      self::win();
    } else {
      self::setMaxSteps(2);
    }
  }

}