<?php

namespace Integration\Cards\Base;

use Innovation\Enums\Locations;
use Integration\BaseIntegrationTest;

class CompleteDogmaTest extends BaseIntegrationTest
{

  public function test_allDogmas_thirdEdition_base()
  {
    error_log("*** test_allDogmas_thirdEdition_echoes ***");
    self::executeCards(range(1, 104));
  }

  public function test_allDogmas_fourthEdition_base()
  {
    error_log("*** test_allDogmas_fourthEdition_echoes ***");
    self::executeCards(array_merge(range(1, 104), range(440, 449)));
  }

  public function test_allDogmas_thirdEdition_artifacts()
  {
    error_log("*** test_allDogmas_thirdEdition_artifacts ***");
    self::executeCards(array_merge(range(110, 187), range(189, 214)));
  }

  public function test_allDogmas_fourthEdition_artifacts()
  {
    error_log("*** test_allDogmas_fourthEdition_artifacts ***");
    // TODO(4E): Add Martian Internet later
    self::executeCards(array_merge(range(110, 187), range(189, 214), [450], range(452, 459)));
  }

  public function test_allDogmas_thirdEdition_echoes()
  {
    error_log("*** test_allDogmas_thirdEdition_echoes ***");
    self::executeCards(range(330, 434));
  }

  public function test_allDogmas_fourthEdition_echoes()
  {
    error_log("*** test_allDogmas_fourthEdition_echoes ***");
    self::executeCards(array_merge(range(330, 434), range(470, 479)));
  }

  public function test_allDogmas_fourthEdition_unseen()
  {
    error_log("*** test_allDogmas_fourthEdition_unseen ***");
    // TODO(4E): Add Hitchhiking and Teleprompter later
    self::executeCards(array_merge(range(480, 559), range(561, 569), range(571, 594)));
  }

  private function executeCards(array $cardIds)
  {
    shuffle($cardIds);

    self::setGlobalVariable("debug_mode", 2);

    error_log("*** STARTING GAME ***");

    $numCardsTested = 0;
    foreach ($cardIds as $cardId) {

      // Handle free action at start of turn
      if (self::getCurrentStateName() === 'artifactPlayerTurn') {
        self::passArtifact();
      }

      $card = $this->tableInstance->getTable()->getCardInfo($cardId);
      $meldedCard = $this->tableInstance->getTable()->meldCard($card, self::getActivePlayerId());
      self::dogma($meldedCard['id']);
      $numCardsTested++;

      if (self::getCurrentStateName() === 'gameEnd') {
        error_log("*** GAME ENDED PREMATURELY ***");
        break;
      }

      foreach (self::getPlayerIds() as $playerId) {
        if (self::getCards(Locations::REVEALED, $playerId)) {
          throw new \RuntimeException("Player $playerId has cards stuck in the revealed zone");
        }
      }
    }

    $totalCards = count($cardIds);
    error_log("*** TESTED $numCardsTested/$totalCards CARDS ***");

  }

  private function passArtifact()
  {
    error_log("* PASS ARTIFACT");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->passArtifactOnDisplay();
    $this->tableInstance->advanceGame();
  }

  private function dogma($cardId)
  {
    $cardName = $this->tableInstance->getTable()->getCardName($cardId);
    error_log("* DOGMA $cardName");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArg('card_id', $cardId)
      ->dogma();
    $this->tableInstance->advanceGame();

    self::executeInteractions();
  }

}