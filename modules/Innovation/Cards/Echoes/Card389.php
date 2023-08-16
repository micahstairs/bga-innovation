<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card389 extends Card
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

  public function getInteractionOptions(): array
  {
    $cardIds = [];
    $playerIds = self::isFirstOrThirdEdition() ? self::getOtherPlayerIds() : self::getOpponentIds();
    foreach ($playerIds as $playerId) {
      $achievementCounts = self::countCardsKeyedByValue('achievements', $playerId);
      foreach (self::getTopCards($playerId) as $card) {
        if ($achievementCounts[$card['faceup_age']] > 0) {
          $cardIds[] = $card['id'];
        }
      }
    }
    self::setAuxiliaryArray($cardIds);
    return [
      'can_pass'                        => true,
      'owner_from'                      => 'any player',
      'location_from'                   => 'board',
      'achieve_if_eligible'             => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::transferToBoard(self::getTopCardOfColor($this->game::GREEN), self::getLastSelectedOwner());
    } else {
      self::drawAndMeld(7);
    }
  }

}