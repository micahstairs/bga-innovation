<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card510 extends AbstractCard
{
  // Smuggling
  //   - I DEMAND you transfer a card of value equal to your top yellow card and a card of value
  //     equal to my top yellow card from your score pile to my score pile!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location' => Locations::SCORE,
        'owner_to' => self::getLauncherId(),
        'age'      => self::getValue(self::getTopCardOfColor(Colors::YELLOW)),
      ];
    } else {
      return [
        'location' => Locations::SCORE,
        'owner_to' => self::getLauncherId(),
        'age'      => self::getValue(self::getTopCardOfColor(Colors::YELLOW, self::getLauncherId())),
      ];
    }
  }

}