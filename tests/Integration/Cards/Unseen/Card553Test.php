<?php

namespace Integration\Cards\Unseen;

use Doctrine\DBAL\Connection;
use Integration\Cards\BaseCardIntegrationTest;

class Card553Test extends BaseCardIntegrationTest
{
  // Fortune Cookie:
  //   - If you have exactly seven of any icon on your board, draw and score a [7]; exactly eight,
  //     splay your green or purple cards right and draw an [8]; exactly nine, draw a [9].

  public function test_whenExactlyEightAndNineButCannotSplay_unseen_fourthEdition_stillDraws9()
  {
    $playerId = self::getActivePlayerId();

    // Fortune Cookie is purple. Keep green/purple as singletons so the eight-splay cannot fire.
    // Four blue cards splayed aslant: 3 buried x 4 spots + top x 6 = 18 visible icons.
    $blueCards = [];
    foreach (self::getCards('deck') as $card) {
      if (intval($card['color']) === 0 && intval($card['type']) === 0) {
        $blueCards[] = $card;
        if (count($blueCards) === 4) {
          break;
        }
      }
    }
    self::assertCount(4, $blueCards);
    foreach ($blueCards as $card) {
      self::debugTransfer(intval($card['id']), 'meld');
    }
    self::debugSplay(0, 4); // blue, aslant

    // Exactly eight of icon 1 and nine of icon 2 (hex 0 is ignored).
    $this->tableInstance->withDbConnection(function (Connection $db) use ($blueCards) {
      $ids = array_map(fn($card) => intval($card['id']), $blueCards);
      $db->executeStatement("UPDATE card SET spot_1 = 2, spot_2 = 2, spot_3 = 2, spot_4 = 2, spot_5 = 0, spot_6 = 0 WHERE id IN ({$ids[0]}, {$ids[1]})");
      $db->executeStatement("UPDATE card SET spot_1 = 1, spot_2 = 1, spot_3 = 1, spot_4 = 0, spot_5 = 0, spot_6 = 0 WHERE id = {$ids[2]}");
      $db->executeStatement("UPDATE card SET spot_1 = 1, spot_2 = 1, spot_3 = 1, spot_4 = 1, spot_5 = 1, spot_6 = 2 WHERE id = {$ids[3]}");
      $db->executeStatement("UPDATE card SET spot_1 = 0, spot_2 = 0, spot_3 = 0, spot_4 = 0, spot_5 = 0, spot_6 = 0 WHERE id = 553");
    });

    $handBefore = self::countCards('hand', $playerId);
    self::dogma();

    self::assertDogmaComplete();
    self::assertGreaterThanOrEqual($handBefore + 1, self::countCards('hand', $playerId), 'Should still draw a 9 when you have exactly nine even if the eight-splay cannot happen');
    self::assertEquals(9, self::getMaxAge('hand', $playerId));
  }
}
