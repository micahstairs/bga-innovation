<?php

namespace Integration\Cards\Artifacts;

use Integration\Cards\BaseCardIntegrationTest;

class Card147Test extends BaseCardIntegrationTest
{
  public function test_onlyOpponentHasEligibleScoreCard_fourthEdition_artifacts_hasEffect()
  {
    $launcherId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();
    self::debugTransfer(1, 'score', $opponentId);
    $game = $this->tableInstance->getTable();
    $info = $game->getDogmaEffectInfo(self::getCard(147), $launcherId);
    self::assertFalse($info['no_effect'], 'An opponent score card can be returned even with an empty own score pile');
    self::dogma(147, $launcherId);
    self::chooseSpecial(1);
    self::assertEquals(0, self::countCards('score', $opponentId));
    self::assertEquals(1, self::countCards('score', $launcherId));
  }
  public function test_onlyFivesInScorePiles_fourthEdition_artifacts_hasNoEffect()
  {
    $launcherId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();
    self::debugTransfer(45, 'score', $launcherId);
    self::debugTransfer(46, 'score', $opponentId);
    $info = $this->tableInstance->getTable()->getDogmaEffectInfo(self::getCard(147), $launcherId);
    self::assertTrue($info['no_effect']);
  }
}
