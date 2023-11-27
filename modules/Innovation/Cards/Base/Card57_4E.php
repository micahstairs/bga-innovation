<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card57_4E extends AbstractCard
{
  // Industrialization (3rd edition):
  //   - Draw and tuck three [6]. Then, if you are the single player with the most [EFFICIENCY], return your top red card.
  //   - You may splay your red or purple cards right.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      for ($i = 0; $i < 3; $i++) {
        self::drawAndTuck(6);
      }
      $iconCounts = self::getStandardIconCountsOfAllPlayers();
      foreach (self::getOtherPlayerIds() as $playerId) {
        if ($iconCounts[$playerId][Icons::EFFICIENCY] >= $iconCounts[self::getPlayerId()][Icons::EFFICIENCY]) {
          return; // If another player has at least as many [EFFICIENCY] as you, do not return your top red card
        }
      }
      self::return(self::getTopCardOfColor(Colors::RED));
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::RIGHT,
      'color'           => [Colors::RED, Colors::PURPLE],
    ];
  }

}