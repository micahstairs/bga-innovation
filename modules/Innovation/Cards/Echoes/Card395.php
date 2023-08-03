<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card395 extends Card
{

  // Photography
  // - 3rd edition
  //   - ECHO: Meld a card from your forecast.
  //   - I DEMAND you take the highest top card from your board into your hand!
  //   - If you have at least three echo effects visible in one color, claim the History achievement.
  // - 4th edition
  //   - ECHO: Meld a card from your forecast.
  //   - I DEMAND you transfer the highest top card from your board to your hand!
  //   - If you have three or more echo effects visible in one color, claim the History achievement.
  //     If you do, and Photography was foreseen, you win.

  public function initialExecution()
  {
    if (self::isEcho() || self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      for ($color = 0; $color < 5; $color++) {
        if ($this->game->countVisibleIconsInPile(self::getPlayerId(), $this->game::ECHO_EFFECT_ICON, $color) >= 3) {
          $this->game->claimSpecialAchievement(self::getPlayerId(), 438); // History
          if (self::wasForeseen()) {
            self::win();
          }
          return;
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'forecast',
        'meld_keyword'  => true,
      ];
    } else {
      // TODO(4E): Test that this behaves correctly with Battleship Yamato.
      return [
        'location_from' => 'board',
        'location_to'   => 'hand',
        'age'           => $this->game->getMaxAgeOnBoardTopCards(self::getPlayerId()),
      ];
    }
  }

}