<?php

namespace Integration\Cards\Base;

use Innovation\Enums\CardTypes;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Integration\Cards\BaseCardIntegrationTest;

class Card20Test extends BaseCardIntegrationTest
{
  // Mapmaking:
  //   - I DEMAND you transfer a [1] from your score pile to my score pile!
  //   - If any card was transferred due to the demand, draw and score a [1]!

  public function test_whenBuriedEchoClearsAux_echoes_fourthEdition_stillDrawsAndScoresAfterDemand()
  {
    $playerId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();
    $mapmaking = self::getCard(20);

    $echoCard = null;
    foreach (self::getCards('deck') as $card) {
      if (intval($card['type']) !== CardTypes::ECHOES || intval($card['color']) !== intval($mapmaking['color'])) {
        continue;
      }
      foreach ([2, 3, 4] as $spot) {
        if (isset($card["spot_$spot"]) && intval($card["spot_$spot"]) === Icons::ECHO_EFFECT) {
          $echoCard = $card;
          break 2;
        }
      }
    }
    self::assertNotNull($echoCard, 'Need a green Echoes card whose echo is visible when splayed up');
    self::debugTransfer(intval($echoCard['id']), 'tuck');
    self::debugSplay(intval($mapmaking['color']), Directions::UP);

    self::scoreFromDeck(1, $opponentId);
    $onesBefore = self::countScoreCardsOfAge(1, $playerId);

    self::dogma(null, $playerId);
    while (self::getCurrentStateName() === 'selectionMove') {
      if (count(self::getSelectedCards()) > 0) {
        self::selectRandomCard();
      } else if (self::getSpecialChoiceType() !== '') {
        self::selectSpecialChoice();
      } else {
        self::pass();
      }
    }

    self::assertDogmaComplete();
    self::assertEquals(
      $onesBefore + 2,
      self::countScoreCardsOfAge(1, $playerId),
      'Demand transfer plus draw-and-score should add two 1s, even after a buried echo'
    );
  }

  private function countScoreCardsOfAge(int $age, int $playerId): int
  {
    $count = 0;
    foreach (self::getCards('score', $playerId) as $card) {
      if (intval($card['age']) === $age) {
        $count++;
      }
    }
    return $count;
  }
}
