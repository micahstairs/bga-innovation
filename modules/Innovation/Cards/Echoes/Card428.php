<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card428 extends Card
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
      $playerIconCounts = $this->game->getPlayerResourceCounts(self::getPlayerId());
      $playerIds = self::isFirstOrThirdEdition() ? self::getOtherPlayerIds() : self::getOpponentIds();
      foreach ($playerIds as $playerId) {
        $otherIconCounts = $this->game->getPlayerResourceCounts($playerId);
        foreach ([$this->game::INDUSTRY, $this->game::PROSPERITY, $this->game::AUTHORITY] as $icon) {
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
        'color' => self::getAllColorsOtherThan($this->game::RED),
      ];
    } else {
      if (self::isFirstInteraction()) {
        // TODO(4E): Confirm whether we need to offer non-standard icons as options for this card.
        return ['choose_icon_type' => true];
      } else {
        return [
          'location_from' => 'board',
          'owner_to' => self::getLauncherId(),
          'location_to' => 'score',
          'without_icon' => self::getAuxiliaryValue(),
        ];
      }
    }
  }

  public function handleSpecialChoice(int $icon) {
    $this->notifications->notifyIconChoice($icon, self::getPlayerId());
    self::setAuxiliaryValue($icon); // Track exempted icon
  }

}