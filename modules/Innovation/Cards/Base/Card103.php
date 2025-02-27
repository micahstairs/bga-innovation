<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;

class Card103 extends AbstractCard
{
  // A. I.
  //   - Draw and score a [10].
  //   - If Robotics and Software are top cards on any board, the single player with the lowest score wins.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(10);
    } else if (self::isSecondNonDemand()) {
      if ($this->game->isTopBoardCard(self::getCard(CardIds::ROBOTICS)) || $this->game->isTopBoardCard(self::getCard(CardIds::SOFTWARE))) {
        $playerWithLowestScore = self::getPlayerWithLowestScore();
        if ($playerWithLowestScore !== null) {
          self::notifyPlayer(clienttranslate('${You} have the lowest score.'), [], $playerWithLowestScore);
          self::notifyOthers(clienttranslate('${player_name} has the lowest score.'), [], $playerWithLowestScore);
          self::win($playerWithLowestScore);
        }
      }
    }
  }

  public function getPlayerWithLowestScore(): ?int
  {
    $minScore = PHP_INT_MAX;
    $playerWithMinScore = null;
    foreach (self::getPlayerIds() as $playerId) {
      $score = self::getScore($playerId);

      $args = ['n' => $score];
      if ($score == 1) {
        self::notifyPlayer(clienttranslate('${You} have 1 point.'), $args, $playerId);
        self::notifyOthers(clienttranslate('${player_name} has 1 point.'), $args, $playerId);
      } else {
        self::notifyPlayer(clienttranslate('${You} have ${n} points.'), $args, $playerId);
        self::notifyOthers(clienttranslate('${player_name} has ${n} points.'), $args, $playerId);
      }

      if ($score < $minScore) {
        $minScore = $score;
        $playerWithMinScore = $playerId;
      } else if ($score == $minScore) {
        $playerWithMinScore = null;
      }
    }
    return $playerWithMinScore;
  }

}