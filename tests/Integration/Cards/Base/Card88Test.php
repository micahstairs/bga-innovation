<?php

namespace Integration\Cards\Base;

use Doctrine\DBAL\Connection;
use Integration\Cards\BaseCardIntegrationTest;

class Card88Test extends BaseCardIntegrationTest
{
  // https://boardgamearena.com/bug?id=234612: when an endorsed Fission demand draws a red
  // card, the dogma action is complete after the junk, so the launcher must NOT execute the
  // second non-demand effect again because the dogma was endorsed.
  public function test_endorsedFissionRedDemand_cities_fourthEdition_doesNotRepeatNonDemandEffect()
  {
    $launcherId = self::getActivePlayerId();
    $playerIds = self::getPlayerIds();
    $opponentId = $playerIds[0] === $launcherId ? $playerIds[1] : $playerIds[0];

    // Give the opponent a card on their board so the junk has an observable effect.
    self::meld(0, $opponentId);

    // The launcher needs a top city featuring Fission's icon (efficiency, 6) to endorse it.
    // The city must not be red, or it would cover Fission on the red stack.
    $cityId = null;
    $this->tableInstance->withDbConnection(function (Connection $db) use (&$cityId) {
      $cityId = intval($db->fetchOne("SELECT id FROM card WHERE type = 2 AND color != 1 AND (spot_1 = 6 OR spot_2 = 6 OR spot_3 = 6 OR spot_4 = 6 OR spot_5 = 6 OR spot_6 = 6) ORDER BY age DESC LIMIT 1"));
    });
    self::assertNotEmpty($cityId, 'Need a city with the efficiency icon');
    self::meld($cityId);

    // Put a red 10 (card 97) on top of the age-10 deck so the opponent's demand draw
    // triggers the junk. (The top of a deck is the highest position.)
    $this->tableInstance->withDbConnection(function (Connection $db) {
      $max = $db->fetchOne("SELECT MAX(position) FROM card WHERE location = 'deck' AND type = 0 AND age = 10");
      $db->executeStatement("UPDATE card SET position = ? WHERE id = 97", [$max + 1]);
    });

    // Pay for the endorse with a card from hand (age 1 is always low enough).
    $handCards = $this->tableInstance->getTable()->getCardsInLocation($launcherId, 'hand');
    $paymentId = intval($handCards[0]['id']);

    // Take the endorse action.
    $this->tableInstance->getTable()->innovationGameState->set('endorse_action_state', 1);
    $this->tableInstance->getTable()->gamestate->changeActivePlayer($launcherId);
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($launcherId)
      ->stubActivePlayerId($launcherId)
      ->stubArgs(['card_to_endorse_id' => 88, 'payment_card_id' => $paymentId])
      ->endorse();
    $this->tableInstance->getTable()->stubCurrentPlayerId($launcherId);
    $this->tableInstance->advanceGame();

    // Drain any interactions (none are expected: after the junk, the dogma is complete).
    $safety = 0;
    while (self::getCurrentStateName() === 'selectionMove' && $safety++ < 10) {
      self::pass();
    }

    // The opponent's demand draw was red: all of their board cards are junked.
    self::assertEquals(0, self::countCards('board', $opponentId), 'Red demand draw should have junked all board cards');

    // The launcher tucked one card to pay and their remaining hand card was junked, so their
    // hand is empty. With bug #234612 the launcher wrongly executes the second non-demand
    // effect again because the dogma was endorsed and draws a 10 (or higher) into hand.
    self::assertEquals(0, self::countCards('hand', $launcherId), 'Endorsed Fission must not repeat the non-demand effect after the junk');
  }
}
