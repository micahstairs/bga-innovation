<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card428 extends AbstractCard
{

  // Social Networking
  // - 3rd edition 
  //   - I DEMAND you choose an icon type! Transfer all top cards without that icon from your board to my score pile!
  //   - If you have fewer [INDUSTRY], fewer [PROSPERITY], and fewer [AUTHORITY] than each other player, you win.
  // - 4th edition
  //   - ECHO: Score a top non-red card from your board.
  //   - I DEMAND you choose an icon type! Transfer a top card without that icon of each color from your board to my score pile!
  //   - If you have fewer [INDUSTRY], fewer [PROSPERITY], and fewer [AUTHORITY] than each opponent, you win.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(2);
    } else {
      $hasFewerIcons = true;
      $playerIconCounts = self::getStandardIconCounts();
      $playerIds = self::isFirstOrThirdEdition() ? self::getOtherPlayerIds() : self::getOpponentIds();
      foreach ($playerIds as $playerId) {
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
      return [
        'location_from' => 'board',
        'score_keyword' => true,
        'color'         => Colors::NON_RED,
      ];
    } else {
      if (self::isFirstInteraction()) {
        // TODO(4E): Offer non-standard icons as options for this card.
        return ['choose_icon_type' => true];
      } else {
        return [
          'n'             => 'all',
          'location_from' => 'board',
          'owner_to'      => self::getLauncherId(),
          'location_to'   => 'score',
          'without_icon'  => self::getAuxiliaryValue(),
        ];
      }
    }
  }

  public function handleIconChoice(int $icon)
  {
    self::notifyIconChoice($icon);
    self::setAuxiliaryValue($icon); // Track exempted icon
  }

}