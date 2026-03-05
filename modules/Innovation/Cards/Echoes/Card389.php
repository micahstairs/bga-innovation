<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card389 extends AbstractCard
{

  // Hot Air Balloon
  // - 3rd edition
  //   - ECHO: Draw and score a [7].
  //   - You may achieve (if eligible) a top card from any other player's board if they have an
  //     achievement of matching value. If you do, transfer your top green card to that player's
  //     board. Otherwise, draw and meld a [7].
  // - 4th edition
  //   - ECHO: Draw and score a [7].
  //   - You may achieve (if eligible) a top card from an opponent's board if they have an
  //     achievement of matching value. If you do, transfer your top green card to that player's
  //     board. Otherwise, draw and meld a [7].

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndScore(7);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    $cardIds = [];
    $playerIds = self::isFirstOrThirdEdition() ? self::getOtherPlayerIds() : self::getOpponentIds();
    foreach ($playerIds as $playerId) {
      $achievementCounts = self::countCardsKeyedByValue('achievements', $playerId);
      foreach (self::getTopCards($playerId) as $card) {
        if ($achievementCounts[self::getFaceupValue($card)] > 0) {
          $cardIds[] = self::getId($card);
        }
      }
    }
    self::setAuxiliaryArray($cardIds);
    return self::youMay()->achieveIfEligible()->fromAnyBoard()->onlyCardsInAuxiliaryArray();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::transferToBoard(self::getTopCardOfColor(Colors::GREEN), self::getLastSelectedOwner());
    } else {
      self::drawAndMeld(7);
    }
  }

}