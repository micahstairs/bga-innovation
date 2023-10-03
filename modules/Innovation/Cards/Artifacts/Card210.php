<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card210 extends AbstractCard
{
  // Seikan Tunnel
  // - 3rd edition:
  //   - If you have the most cards of a color showing on your board out of all colors on all boards, you win.
  // - 4th edition:
  //   - If you have the most cards of a color visible on your board out of all colors on all boards, you win.

  public function initialExecution()
  {
    $hasMaxVisibleStackSize = true;
    $maxVisibleStackSize = self::getMaxVisibleStackSize(self::getPlayerId());
    foreach (self::getOtherPlayerIds() as $playerId) {
      if (self::getMaxVisibleStackSize($playerId) > $maxVisibleStackSize) {
        $hasMaxVisibleStackSize = false;
        break;
      }
    }

    if ($hasMaxVisibleStackSize) {
      self::notifyPlayer(clienttranslate('${You} have the most cards of a color showing on your board out of all colors on all boards.'));
      self::notifyOthers(clienttranslate('${player_name} has the most cards of a color showing on his board out of all colors on all boards.'));
      self::win();
    } else {
      self::notifyPlayer(clienttranslate('${You} do not have the most cards of a color showing on your board out of all colors on all boards.'));
      self::notifyOthers(clienttranslate('${player_name} does not have the most cards of a color showing on his board out of all colors on all boards.'));
    }
  }

  private function getMaxVisibleStackSize(int $playerId): int
  {
    $maxCount = 0;
    foreach (self::countCardsKeyedByColor(Locations::BOARD, $playerId) as $count) {
      $maxCount = max($maxCount, $count);
    }
    return $maxCount;
  }

}