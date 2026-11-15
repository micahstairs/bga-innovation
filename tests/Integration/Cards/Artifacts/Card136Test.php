<?php

namespace Integration\Cards\Artifacts;

use Doctrine\DBAL\Connection;
use Integration\Cards\BaseCardIntegrationTest;

class Card136Test extends BaseCardIntegrationTest
{
  // https://boardgamearena.com/bug?id=249052: revealing a card does not count for
  // the sharing bonus (rulebook p.11).

  public function test_sharedEffectOnlyRevealsCard_artifacts_fourthEdition_noSharingBonus()
  {
    $launcherId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();

    // Give the opponent two board cards with enough concept icons to share Yata's effect.
    self::debugTransfer(14, 'meld', $opponentId);
    self::debugTransfer(15, 'meld', $opponentId);

    $this->tableInstance->withDbConnection(function (Connection $db) use ($opponentId) {
      // Yata's featured icon is concept (3); make the opponent's board full of it and non-red.
      $db->executeStatement("UPDATE card SET spot_1 = 3, spot_2 = 3, spot_3 = 3, spot_4 = 3, color = 2 WHERE owner = {$opponentId} AND location = 'board'");
      // Force both hands to red so that no top card of the revealed color exists on any board.
      $db->executeStatement("UPDATE card SET color = 1 WHERE location = 'hand'");
    });

    // Yata's featured icon is concept (3); give the opponent enough to share.
    $this->tableInstance->withDbConnection(function (Connection $db) use ($launcherId, $opponentId) {
      $db->executeStatement("UPDATE player SET player_icon_count_3 = 1 WHERE player_id = {$launcherId}");
      $db->executeStatement("UPDATE player SET player_icon_count_3 = 5 WHERE player_id = {$opponentId}");
    });

    $handBefore = self::countCards('hand', $launcherId);
    self::dogma(136, $launcherId);
    while (self::getCurrentStateName() === 'selectionMove') {
      self::selectRandomCard();
    }

    // Each player only revealed a card, which does not count for the sharing bonus.
    self::assertEquals($handBefore, self::countCards('hand', $launcherId), 'Reveal-only sharing must not award a bonus draw');
  }
}
