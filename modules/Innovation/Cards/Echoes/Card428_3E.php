<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card428_3E extends AbstractCard
{

  // Social Networking (3rd edition)
  //   - I DEMAND you choose an icon type! Transfer all top cards without that icon from your board to my score pile!
  //   - If you have fewer [INDUSTRY], fewer [PROSPERITY], and fewer [AUTHORITY] than each other player, you win.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(2);
    } else {
      $hasFewerIcons = true;
      $playerIconCounts = self::getStandardIconCounts();
      $playerIds = self::getOtherPlayerIds();
      foreach (self::getOtherPlayerIds() as $playerId) {
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

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMust()->score()->non(Colors::RED)->fromYourBoard();
    } else {
      if (self::isFirstInteraction()) {
        return self::youMust()->chooseIcon();
      } else {
        return self::youMust()->all()->withoutIcon(self::getAuxiliaryValue())->fromYourBoard()->toMyScore();
      }
    }
  }

  public function handleIconChoice(int $icon)
  {
    self::notifyIconChoice($icon);
    self::setAuxiliaryValue($icon); // Track exempted icon
  }

}