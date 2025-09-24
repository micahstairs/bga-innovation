<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card428_4E extends AbstractCard
{

  // Social Networking (4th edition)
  //   - ECHO: Score a top non-red card from your board.
  //   - I DEMAND you choose a standard icon type! Transfer all top cards without that icon from your board to my score pile!
  //   - If you have fewer [INDUSTRY], fewer [PROSPERITY], and fewer [AUTHORITY] than each opponent, you win.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      $hasFewerIcons = true;
      $playerIconCounts = self::getStandardIconCounts();
      foreach (self::getOpponentIds() as $playerId) {
        $otherIconCounts = self::getStandardIconCounts($playerId);
        foreach ([Icons::INDUSTRY, Icons::PROSPERITY, Icons::AUTHORITY] as $icon) {
          if ($otherIconCounts[$icon] <= $playerIconCounts[$icon]) {
            $hasFewerIcons = false;
          }
        }
      }
      if ($hasFewerIcons) {
        self::win();
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return self::youMust()->score()->non(Colors::RED)->fromYourBoard()->build();
    } else {
      return self::youMust()->chooseIcon()->build();
    }
  }

  public function handleIconChoice(int $icon)
  {
    self::notifyIconChoice($icon);
    foreach (Colors::ALL as $color) {
      while ($card = self::getTopCardOfColor($color)) {
        if (self::hasIcon($card, $icon)) {
          break;
        } else {
          self::transferToScorePile($card, self::getLauncherId());
        }
      }
    }
  }

}