<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card171_3E extends AbstractCard
{
  // Stamp Act (3rd edition):
  //   - I COMPEL you to transfer a card of value equal to the top yellow card on your board from
  //     your score pile to mine! If you do, return a card from your score pile of value equal to
  //     the top green card on your board!

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      $value = self::getValue(self::getTopCardOfColor(Colors::YELLOW));
      return self::youMust()->value($value)->fromYourScore()->toMine();
    } else {
      $value = self::getValue(self::getTopCardOfColor(Colors::GREEN));
      return self::youMust()->value($value)->fromYourScore()->toMine();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

  public function compelMightBeEffective(): bool
  {
    $value = self::getValue(self::getTopCardOfColor(Colors::YELLOW));
    return self::countCardsKeyedByValue(Locations::SCORE)[$value] > 0;
  }

}