<?php

namespace Integration\Cards\Cities;

use Doctrine\DBAL\Connection;
use Innovation\Enums\CardTypes;
use Integration\Cards\BaseCardIntegrationTest;

class Card220Test extends BaseCardIntegrationTest
{
  // https://boardgamearena.com/bug?id=250955: revealing cards for a search icon is
  // not a draw, so it must not consume the player's pending Unseen draw.

  public function test_meldingCityWithSearchIcon_cities_unseen_fourthEdition_preservesUnseenDraw()
  {
    $playerId = self::getActivePlayerId();

    // Give every age 1 base card an authority icon so the revealed card matches the
    // search and does not have to be returned.
    $this->tableInstance->withDbConnection(function (Connection $db) {
      $db->executeStatement("UPDATE card SET spot_1 = 4 WHERE age = 1 AND type = 0");
    });

    // Card 220 is an age 1 city with a search icon (authority icon in the top-middle).
    self::debugTransfer(220, 'draw', $playerId);

    // The debug draw may have consumed the flag; restore the pending Unseen draw.
    $this->tableInstance->withDbConnection(function (Connection $db) use ($playerId) {
      $db->executeStatement("UPDATE player SET will_draw_unseen_card_next = TRUE WHERE player_id = {$playerId}");
    });

    // Use the real meld action so the city's search-icon effect runs.
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => 220])
      ->meld();

    // Do not advance the game state: a turn transition would reset the Unseen flag
    // and mask the bug. The search-icon effect runs synchronously inside meld.

    // Hand starts at 2, the melded city leaves it, and the search icon reveals and
    // keeps one matching card.
    self::assertEquals(3, self::countCards('hand', $playerId), 'Search icon should have revealed and kept a card');

    // The first real draw of the turn must still come from the Unseen deck.
    $card = $this->tableInstance->getTable()->executeDraw($playerId, 1);
    self::assertEquals(CardTypes::UNSEEN, intval($card['type']), 'Search icon wrongly consumed the Unseen draw');
    self::assertFalse($this->tableInstance->getTable()->getPlayerWillDrawUnseenCardNext($playerId));
  }
}
