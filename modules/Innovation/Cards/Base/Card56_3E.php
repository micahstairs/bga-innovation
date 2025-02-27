<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card56_3E extends AbstractCard
{
  // Encyclopedia (3rd edition):
  //   - You may meld all the highest cards in your score pile. If you meld one of the highest, you
  //     must meld all of the highest.

  public function getInteractionOptions(): array
  {
    return self::youMay()->meld()->all()->highest()->fromYourScore()->build();
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}