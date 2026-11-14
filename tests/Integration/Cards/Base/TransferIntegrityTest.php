<?php

namespace Integration\Cards\Base;

use Doctrine\DBAL\Connection;
use Integration\Cards\BaseCardIntegrationTest;

class TransferIntegrityTest extends BaseCardIntegrationTest
{
  public function test_meldFromRemovedOntoNonEmptyPile_positionsUnique()
  {
    $playerId = self::getActivePlayerId();
    self::meld(3, $playerId);
    self::meld(17, $playerId);
    $this->removeCard(4);
    $this->tableInstance->getTable()->meldCard(self::getCard(4), $playerId);
    $this->assertPositionsUnique($playerId);
  }

  public function test_meldFromRemovedOntoSingleCardPile_positionsUnique()
  {
    $playerId = self::getActivePlayerId();
    self::meld(3, $playerId);
    $this->removeCard(4);
    $this->tableInstance->getTable()->meldCard(self::getCard(4), $playerId);
    $this->assertPositionsUnique($playerId);
  }

  private function removeCard(int $cardId): void
  {
    $this->tableInstance->withDbConnection(function (Connection $db) use ($cardId) {
      $db->executeStatement("UPDATE card SET owner = 0, location = 'removed', position = 0 WHERE id = {$cardId}");
    });
  }

  private function assertPositionsUnique(int $playerId): void
  {
    $pile = [];
    $this->tableInstance->withDbConnection(function (Connection $db) use ($playerId, &$pile) {
      $pile = $db->fetchAllAssociative("SELECT id, position FROM card WHERE owner = {$playerId} AND location = 'board' AND color = 1 ORDER BY position");
    });
    $positions = array_column($pile, 'position');
    self::assertEquals(count($positions), count(array_unique($positions)), 'Duplicate board positions: ' . json_encode($pile));
  }
}
