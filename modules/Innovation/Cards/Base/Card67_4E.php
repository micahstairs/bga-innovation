<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card67_4E extends AbstractCard
{
  // Combustion (4th edition):
  //   - I DEMAND you transfer one card from your score pile to my score pile for every color with
  //     [PROSPERITY] on my board!
  //   - Return your bottom red card.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      self::return(self::getBottomCardOfColor(Colors::RED));
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'        => self::countColorsWithIcon(Icons::PROSPERITY),
      'location' => Locations::SCORE,
      'owner_to' => self::getLauncherId(),
    ];
  }

}