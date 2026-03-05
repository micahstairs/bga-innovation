<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card158 extends AbstractCard
{
  // Ship of the Line Sussex
  // - 3rd edition:
  //   - If you have no cards in your score pile, choose a color and score all cards of that color
  //     from your board. Otherwise, return all cards from your score pile.
  // - 4th edition:
  //   - If you have no cards in your score pile, choose a color and score all cards of that color
  //     on your board. Otherwise, return all cards from your score pile.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::countCards(Locations::SCORE) === 0) {
      return self::youMust()->chooseColor();
    } else {
      return self::youMust()->return()->all()->fromYourScore();
    }
  }

  public function handleColorChoice(int $color)
  {
    foreach (array_reverse(self::getStack($color)) as $card) {
      self::score($card);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE) || self::hasCards(Locations::BOARD);
  }
}
