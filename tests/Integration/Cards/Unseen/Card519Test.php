<?php

namespace Integration\Cards\Unseen;

use Doctrine\DBAL\Connection;
use Integration\Cards\BaseCardIntegrationTest;

class Card519Test extends BaseCardIntegrationTest
{
  // Blackmail's demand makes the opponent self-execute a card. That nested
  // non-demand execution must NOT earn the launcher a sharing bonus, because
  // the outermost effect is a demand.

  public function test_demandNestedSelfExecute_unseen_fourthEdition_noSharingBonus()
  {
    $launcherId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();

    // Give the opponent a score pile so that both revealed zones are non-empty.
    self::debugTransfer(self::drawBaseCard(1, $opponentId)['id'], 'score', $opponentId);

    // Force the demand to hit the opponent: Blackmail's dogma icon must be
    // scarcer on the opponent's board than on the launcher's.
    $dogmaIcon = intval(self::getCard(519)['dogma_icon']);
    $this->tableInstance->withDbConnection(function (Connection $db) use ($launcherId, $opponentId, $dogmaIcon) {
      $db->executeStatement("UPDATE card SET spot_1 = {$dogmaIcon}, spot_2 = {$dogmaIcon}, spot_3 = {$dogmaIcon}, spot_4 = {$dogmaIcon}, spot_5 = NULL, spot_6 = NULL WHERE owner = {$launcherId} AND location = 'board'");
      $db->executeStatement("UPDATE player SET player_icon_count_{$dogmaIcon} = 9 WHERE player_id = {$launcherId}");
      $db->executeStatement("UPDATE player SET player_icon_count_{$dogmaIcon} = 0 WHERE player_id = {$opponentId}");
    });

    $handBefore = self::countCards('hand', $launcherId);
    self::dogma(519, $launcherId);
    $guard = 0;
    while (self::getCurrentStateName() === 'selectionMove' && $guard++ < 30) {
      if (self::canDoSpecialChoice()) {
        self::selectSpecialChoice();
      } else {
        self::selectRandomCard();
      }
    }

    self::assertEquals('playerTurn', self::getCurrentStateName());
    self::assertEquals(1, self::getGlobalVariable('dogma_had_impact'), 'The demand executed with impact');
    self::assertEquals($handBefore, self::countCards('hand', $launcherId), 'A demand must never award a sharing bonus');
  }
}
