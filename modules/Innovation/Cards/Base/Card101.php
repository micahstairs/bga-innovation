<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card101 extends AbstractCard
{
  // Globalization:
  // - 3rd edition:
  //   - I DEMAND you return a top card with a [HEALTH] on your board!
  //   - Draw and score a [6]. If no player has more [HEALTH] than [INDUSTRY] on their board, the
  //     single player with the most points wins.
  // - 4th edition:
  //   - I DEMAND you return a top card with [HEALTH] from your board!
  //   - Draw and meld an [11]. If no player has more [HEALTH] than [INDUSTRY] on their board, the
  //     single player with the most points wins.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      if (self::isFourthEdition()) {
        self::drawAndMeld(11);
      } else {
        self::drawAndScore(6);
      }
      if (!self::someoneHasMoreHealthThanIndustry()) {
        $playersWithMostPoints = self::getPlayersWithMostPoints();
        if (count($playersWithMostPoints) == 1) {
          $playerId = $playersWithMostPoints[0];
          self::notifyOthers(clienttranslate('${player_name} has a greater score than each other player.'), [], $playerId);
          self::notifyPlayer(clienttranslate('${You} have a greater score than each other player.'), [], $playerId);
          self::win($playerId);
        } else {
          self::notifyAll(clienttranslate('There is a tie for the greatest score. The game continues.'));
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->return()->withIcon(Icons::HEALTH)->fromYourBoard()->build();
  }

  public function someoneHasMoreHealthThanIndustry(): bool
  {
    $iconCountsByPlayer = self::getStandardIconCountsOfAllPlayers();
    foreach ($iconCountsByPlayer as $player => $iconCounts) {
      $args = ['icon_1' => Icons::render(Icons::HEALTH), 'icon_2' => Icons::render(Icons::INDUSTRY)];
      if ($iconCounts[Icons::HEALTH] > $iconCounts[Icons::INDUSTRY]) {
        self::notifyPlayer('${You} have more ${icon_1} than ${icon_2}.', $args);
        self::notifyOthers('${player_name} has more ${icon_1} than ${icon_2}.', $args);
        return true;
      } else {
        self::notifyPlayer('${You} do not have more ${icon_1} than ${icon_2}.', $args);
        self::notifyOthers('${player_name} does not have more ${icon_1} than ${icon_2}.', $args);
      }
    }
    return false;
  }

  public function getPlayersWithMostPoints(): array
  {
    $playersWithMostPoints = [];
    $maxPoints = 0;
    foreach (self::getPlayerIds() as $playerId) {
      $points = self::getScore($playerId);
      if ($points > $maxPoints) {
        $maxPoints = $points;
        $playersWithMostPoints = [$playerId];
      } else if ($points == $maxPoints) {
        $playersWithMostPoints[] = $playerId;
      }
    }
    return $playersWithMostPoints;
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::filterByIcon(self::getTopCards(), Icons::HEALTH)) > 0;
  }

}