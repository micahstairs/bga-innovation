<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card95 extends AbstractCard
{
  // Bioengineering:
  // - 3rd edition:
  //   - Transfer a top card with a [HEALTH] from any opponent's board to your score pile.
  //   - If any player has fewer than three [HEALTH] on their board, the single player with the
  //     most [HEALTH] on their board wins.
  // - 4th edition:
  //   - Score a top card with [HEALTH] on any opponent's board.
  //   - If any player has fewer than two [HEALTH] on their board, the single player with the
  //     most [HEALTH] on their board wins.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      if (self::anyPlayerHasFewerHealth()) {
        $playerId = self::getPlayerWithMostHealth();
        if ($playerId != null) {
          self::win($playerId);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::youMust()->withIcon(Icons::HEALTH)->fromOpponentsBoard()->toYourScore()->build();
    } else {
      return self::youMust()->score()->withIcon(Icons::HEALTH)->fromOpponentsBoard()->build();
    }

  }

  private function anyPlayerHasFewerHealth(): bool
  {
    $threshold = self::isFourthEdition() ? 2 : 3;
    foreach (self::getPlayerIds() as $playerId) {
      if (self::getStandardIconCount(Icons::HEALTH, $playerId) < $threshold) {
        return true;
      }
    }
    return false;
  }

  private function getPlayerWithMostHealth(): ?int
  {
    $maxHealth = -1;
    $maxPlayerId = null;
    foreach (self::getPlayerIds() as $playerId) {
      $health = self::getStandardIconCount(Icons::HEALTH, $playerId);
      if ($health > $maxHealth) {
        $maxHealth = $health;
        $maxPlayerId = $playerId;
      } else if ($health == $maxHealth) {
        $maxPlayerId = null;
      }
    }
    return $maxPlayerId;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    foreach (self::getOpponentIds() as $opponentId) {
      foreach (self::getTopCards($opponentId) as $card) {
        if (self::hasIcon($card, Icons::HEALTH)) {
          return true;
        }
      }
    }
    return self::anyPlayerHasFewerHealth() && self::getPlayerWithMostHealth() != null;
  }

}