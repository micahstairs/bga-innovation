<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card171_3E extends AbstractCard
{
  // Stamp Act (3rd edition):
  //   - I COMPEL you to transfer a card of value equal to the top yellow card on your board from
  //     your score pile to mine! If you do, return a card from your score pile of value equal to
  //     the top green card on your board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location'   => Locations::SCORE,
        'owner_from' => self::getPlayerId(),
        'owner_to'   => self::getLauncherId(),
        'age'        => self::getValue(self::getTopCardOfColor(Colors::YELLOW)),
      ];
    } else {
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'age'            => self::getValue(self::getTopCardOfColor(Colors::GREEN))
      ];
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