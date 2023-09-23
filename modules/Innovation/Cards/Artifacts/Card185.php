<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card185 extends AbstractCard
{
  // Parnell Pitch Drop
  // - 3rd edition:
  //   - Draw and meld a card of value one higher than the highest top card on your board. If the
  //     melded card has three [EFFICIENCY], you win.
  // - 4th edition:
  //   - Draw and meld a card of value one higher than the highest top card on your board. Junk an
  //     available standard achievement. If you don't, and the melded card has three [EFFICIENCY],
  //     you win.

  public function initialExecution()
  {
    $value = self::getMaxValue(self::getTopCards()) + 1;
    $card = self::drawAndMeld($value);
    if (self::isFirstOrThirdEdition() || !self::getAvailableStandardAchievements()) {
      if ($this->game->countIconsOnCard($card, Icons::EFFICIENCY) === 3) {
        $args = ['icon' => Icons::render(Icons::EFFICIENCY)];
        self::notifyPlayer(clienttranslate('${You} melded a card with three ${icon}.'), $args);
        self::notifyOthers(clienttranslate('${player_name} melded a card with three ${icon}.'), $args);
        self::win();
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
      'junk_keyword'  => true,
    ];
  }

}