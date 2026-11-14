<?php

namespace Integration\Cards\Base;

use Doctrine\DBAL\Connection;
use Innovation\Enums\Locations;
use Integration\Cards\BaseCardIntegrationTest;

class Card100Test extends BaseCardIntegrationTest
{
  // https://boardgamearena.com/bug?id=248348: when the shared effect makes the
  // opponent self-execute another card, that nested execution's impact must
  // still earn the launcher a sharing bonus.

  public function test_sharedSelfExecuteWithImpact_fourthEdition_awardsSharingBonus()
  {
    $launcherId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();

    // Self Service is the card under test (melded by setUp). Give the opponent a
    // top card with guaranteed impact to self-execute: Archery (id 3) junks an
    // available achievement of value 1 or 2.
    self::debugTransfer(3, 'meld', $opponentId);

    // Give each player a high-value achievement so Self Service's win condition
    // cannot fire and so that achievements of value 1 and 2 remain available.
    $achievements = [];
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
      if ($card['age'] !== null && intval($card['age']) >= 8) {
        $achievements[] = intval($card['id']);
      }
    }
    self::debugTransfer($achievements[0], 'achieve', $launcherId);
    self::debugTransfer($achievements[1], 'achieve', $opponentId);

    // Self Service's dogma icon is 1. Make the launcher show exactly one icon 1
    // and the opponent's board full of icon 1, and align the cached player icon
    // counts, so the opponent is eligible to share.
    $this->tableInstance->withDbConnection(function (Connection $db) use ($launcherId, $opponentId) {
      $db->executeStatement("UPDATE card SET spot_1 = 2, spot_2 = 1, spot_3 = 2, spot_4 = 2, spot_5 = NULL, spot_6 = NULL WHERE id = 100");
      $db->executeStatement("UPDATE card SET spot_1 = 1, spot_2 = 1, spot_3 = 1, spot_4 = 1, spot_5 = NULL, spot_6 = NULL WHERE owner = {$opponentId} AND location = 'board'");
      $db->executeStatement("UPDATE player SET player_icon_count_1 = 1 WHERE player_id = {$launcherId}");
      $db->executeStatement("UPDATE player SET player_icon_count_1 = 9 WHERE player_id = {$opponentId}");
    });

    $handBefore = self::countCards('hand', $launcherId);
    $junkBefore = self::countCards('junk', 0);
    self::dogma(100, $launcherId);
    $guard = 0;
    while (self::getCurrentStateName() === 'selectionMove' && $guard++ < 20) {
      self::selectRandomCard();
    }

    self::assertEquals($junkBefore + 1, self::countCards('junk', 0), 'Opponent self-executed Archery and junked an achievement');
    self::assertEquals($handBefore + 1, self::countCards('hand', $launcherId), 'Impactful shared self-execute should award the launcher a bonus draw');
  }
}
