<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card168 extends AbstractCard
{
  // U.S. Declaration of Independence
  //   - I COMPEL you to transfer the highest card in your hand to my hand, the highest card in
  //     your score pile to my score pile, and the highest top card with [INDUSTRY] from your
  //     board to my board!

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location'   => Locations::HAND,
        'owner_from' => self::getPlayerId(),
        'owner_to'   => self::getLauncherId(),
        'age'        => self::getMaxValueInLocation(Locations::HAND),
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location'   => Locations::SCORE,
        'owner_from' => self::getPlayerId(),
        'owner_to'   => self::getLauncherId(),
        'age'        => self::getMaxValueInLocation(Locations::SCORE),
      ];
    } else {
      return [
        'location'   => Locations::BOARD,
        'owner_from' => self::getPlayerId(),
        'owner_to'   => self::getLauncherId(),
        'age'        => $this->game->getMaxAgeOnBoardTopCardsWithIcon(self::getPlayerId(), Icons::INDUSTRY),
        'with_icon'  => Icons::INDUSTRY,
      ];
    }
  }

  public function compelMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::INDUSTRY)) {
        return true;
      }
    }
    return self::countCards(Locations::HAND) > 0 || self::countCards(Locations::SCORE) > 0;
  }

}