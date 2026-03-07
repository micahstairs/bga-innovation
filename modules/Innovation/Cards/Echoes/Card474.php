<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card474 extends AbstractCard
{

  // Algocracy
  //   - Choose an icon type. Transfer all cards with that featured icon from all hands and score
  //     piles to the hand of the single player with the most of the chosen icon on their board.

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->chooseIcon();
  }

  public function handleIconChoice(int $icon)
  {
    self::notifyIconChoice($icon);
    $maxIconCount = 0;
    $maxIconPlayerId = null;
    $multiplePlayersTied = true;
    foreach (self::getPlayerIds() as $playerId) {
      $iconCount = self::getStandardIconCount($icon, $playerId);
      if ($iconCount > $maxIconCount) {
        $maxIconCount = $iconCount;
        $maxIconPlayerId = $playerId;
        $multiplePlayersTied = false;
      } else if ($iconCount === $maxIconCount) {
        $multiplePlayersTied = true;
      }
    }

    if ($multiplePlayersTied) {
      return;
    }

    foreach (self::getOtherPlayerIds($maxIconPlayerId) as $playerId) {
      self::revealHand($playerId);
      foreach (self::getCards(Locations::HAND, $playerId) as $card) {
        self::transferToHandIfFeaturedIconMatches($card, $icon, $maxIconPlayerId);
      }
      self::revealScorePile($playerId);
      foreach (self::getCards(Locations::SCORE, $playerId) as $card) {
        self::transferToHandIfFeaturedIconMatches($card, $icon, $maxIconPlayerId);
      }
    }

    self::revealScorePile($maxIconPlayerId);
    foreach (self::getCards(Locations::SCORE, $maxIconPlayerId) as $card) {
      self::transferToHandIfFeaturedIconMatches($card, $icon, $maxIconPlayerId);
    }


  }

  private function transferToHandIfFeaturedIconMatches(array $card, int $icon, int $playerId)
  {
    if ($card['dogma_icon'] == $icon) {
      self::transferToHand($card, $playerId);
    }
  }

}