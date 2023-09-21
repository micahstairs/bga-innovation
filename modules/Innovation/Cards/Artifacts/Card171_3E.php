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
      $topYellowCard = self::getTopCardOfColor(Colors::YELLOW);
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => Locations::SCORE,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::SCORE,
        'age'           => $topYellowCard ? $topYellowCard['faceup_age'] : 0,
      ];
    } else {
      $topGreenCard = self::getTopCardOfColor(Colors::YELLOW);
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
        'age'            => $topGreenCard['faceup_age'],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}