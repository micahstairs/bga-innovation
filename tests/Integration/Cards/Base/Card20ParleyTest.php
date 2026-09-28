<?php

namespace Integration\Cards\Base;

use BGAWorkbench\Test\TableInstanceBuilder;
use Integration\Cards\BaseCardIntegrationTest;

class Card20ParleyTest extends BaseCardIntegrationTest
{
  // Cities distance rule: a non-adjacent player may return a card to avoid a demand.
  // Returning that card must not count as Mapmaking's demand transfer.

  protected function createGameTableInstanceBuilder(): TableInstanceBuilder
  {
    return $this->gameTableInstanceBuilder()
      ->setPlayersWithIds([12345, 67890, 11111, 22222])
      ->overrideGlobalsPreSetup(self::getGameOptions());
  }

  public function test_whenParleyAvoidsDemand_cities_fourthEdition_doesNotScoreFromTheReturnedCard()
  {
    $launcherId = self::getActivePlayerId();
    $playerIds = self::getPlayerIds();
    self::assertCount(4, $playerIds);

    $nonAdjacentId = null;
    foreach ($playerIds as $playerId) {
      if ($playerId === $launcherId) {
        continue;
      }
      $distance = $this->tableInstance->getTable()->getPlayerTableColumn($playerId, 'player_index');
      $launcherIndex = $this->tableInstance->getTable()->getPlayerTableColumn($launcherId, 'player_index');
      $n = count($playerIds);
      $steps = min(($distance - $launcherIndex + $n) % $n, ($launcherIndex - $distance + $n) % $n);
      if ($steps > 1) {
        $nonAdjacentId = $playerId;
        break;
      }
    }
    self::assertNotNull($nonAdjacentId, 'Need a non-adjacent player for Parley');

    self::scoreFromDeck(1, $nonAdjacentId);
    self::setHandSize(2, $nonAdjacentId);
    $scoreBefore = self::getScore($launcherId);

    self::dogma(null, $launcherId);

    // Non-adjacent player is prompted to return a card to avoid the demand.
    self::assertEquals('selectionMove', self::getCurrentStateName());
    self::assertEquals($nonAdjacentId, self::getActivePlayerId());
    self::selectRandomCard();

    while (self::getCurrentStateName() === 'selectionMove') {
      if (count(self::getSelectedCards()) > 0) {
        self::selectRandomCard();
      } else {
        self::pass();
      }
    }

    self::assertDogmaComplete();
    self::assertEquals($scoreBefore, self::getScore($launcherId), 'Parley return must not count as Mapmaking\'s demand transfer');
    self::assertEquals(1, self::countCards('score', $nonAdjacentId), 'The 1 should still be in the parleying player\'s score pile');
  }
}
