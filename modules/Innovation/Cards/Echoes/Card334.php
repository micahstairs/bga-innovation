<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card334 extends AbstractCard
{

  // Candles
  // - 3rd edition:
  //   - ECHO: If every other player has a higher score than you, draw a [3].
  //   - I DEMAND you transfer a card with a [AUTHORITY] from your hand to my hand! If you do, draw a [1]!
  // - 4th edition:
  //   - ECHO: If no player has fewer points than you, draw a [3].
  //   - I DEMAND you transfer a card with [AUTHORITY] or [CONCEPT] from your hand to my hand! If you do, draw a [1]!

  public function initialExecution()
  {
    if (self::isEcho()) {
      $playerScore = self::getScore();
      $minScore = true;
      foreach (self::getOtherPlayerIds() as $otherPlayerId) {
        $otherPlayerScore = self::getScore($otherPlayerId);
        if (self::isFirstOrThirdEdition() && $otherPlayerScore <= $playerScore) {
          $minScore = false;
        } else if (self::isFourthEdition() && $otherPlayerScore < $playerScore) {
          $minScore = false;
        }
      }
      if ($minScore) {
        self::draw(3);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::youMust()->withIcon(Icons::AUTHORITY)->fromYourHand()->toMine()->revealingIfUnable()->build();
    } else {
      return self::youMust()->withIcons([Icons::AUTHORITY, Icons::CONCEPT])->fromYourHand()->toMine()->revealingIfUnable()->withoutAutoselection()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::draw(1);
  }

}