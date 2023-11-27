<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card93_4E extends AbstractCard
{
  // Services (4th edition):
  //   - I DEMAND you transfer all the cards of the value of my choice from your score pile to my
  //     hand! If you do, transfer a top card without [HEALTH] from my board to your hand!

  public function initialExecution()
  {
    if (self::countCards(Locations::SCORE)) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'player_id'    => self::getLauncherId(),
        'choose_value' => true,
      ];
    } else {
      return [
        'owner_from'    => self::getLauncherId(),
        'location_from' => Locations::BOARD,
        'owner_to'      => self::getPlayerId(),
        'location_to'   => Locations::HAND,
        'without_icon'  => Icons::HEALTH,
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
      self::transferToHand($card, self::getLauncherId());
    }
  }

}