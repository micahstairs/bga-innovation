<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class RandomGameTest extends BaseIntegrationTest
{

  public function test_randomGame_thirdEdition_artifacts_cities_echoes()
  {
    error_log("*** test_randomGame_thirdEdition_artifacts_cities_echoes ***");
    self::executeGame();
  }

  public function test_randomGame_thirdEdition_artifactsWithRelics()
  {
    error_log("*** test_randomGame_thirdEdition_artifactsWithRelics ***");
    self::executeGame();
  }

  public function test_randomGame_fourthEdition_artifacts_cities_echoes_unseen()
  {
    error_log("*** test_randomGame_fourthEdition_artifacts_cities_echoes_unseen ***");
    self::executeGame();
  }

  private function executeGame()
  {
    error_log("*** STARTING GAME ***");
    $edition = $this->tableInstance->getTable()->innovationGameState->getEdition();
    while (self::getCurrentStateName() !== 'gameEnd') {

      // Handle free action at start of turn
      if (self::getCurrentStateName() === 'artifactPlayerTurn') {
        $actions = [
          [$this, 'dogmaArtifact'],
          [$this, 'passArtifact'],
        ];
        if ($edition <= 3) {
          $actions[] = [$this, 'returnArtifact'];
        }
        $actions[array_rand($actions)]();
        if (self::getCurrentStateName() === 'gameEnd') {
          break;
        }
      }

      $actions = [
        [$this, 'draw'],
      ];
      if (self::countCards('hand') > 0) {
        $actions[] = [$this, 'meld'];
      }
      if (count(self::getCardsToDogma()) > 0) {
        $actions[] = [$this, 'dogma'];
      }
      if (count(self::getClaimableStandardAchievementValues()) > 0) {
        $actions[] = [$this, 'achieveStandardAchievement'];
      }
      if (count(self::getClaimableSecretValues()) > 0) {
        $actions[] = [$this, 'achieveSecret'];
      }
      // TODO: Add other actions here (e.g. endorse)
      $actions[array_rand($actions)]();

      foreach (self::getPlayerIds() as $playerId) {
        if (self::getCards(Locations::REVEALED, $playerId)) {
          throw new \RuntimeException("Player $playerId has cards stuck in the revealed zone");
        }
      }
    }

    // TODO: Add better game end info (including the max age dogma'd). Also stop hard-coding the player numbers.
    error_log("*** GAME OVER ***");
    error_log("Player #1 score: " . $this->tableInstance->getTable()->getPlayerScore(12345));
    error_log("Player #1 achievements: " . $this->tableInstance->getTable()->countCardsInLocation(12345, 'achievements'));
    error_log("Player #2 score: " . $this->tableInstance->getTable()->getPlayerScore(67890));
    error_log("Player #2 achievements: " . $this->tableInstance->getTable()->countCardsInLocation(67890, 'achievements'));
  }

  private function dogmaArtifact()
  {
    $cardId = self::getRandomCardId(self::getCards('display'));

    // Make sure we don't try to dogma Battleship Yamato
    if ($cardId == 188) {
      $edition = $this->tableInstance->getTable()->innovationGameState->getEdition();
      if ($edition <= 3) {
        self::returnArtifact();
      } else {
        self::passArtifact();
      }
      return;
    }

    $cardName = $this->tableInstance->getTable()->getCardName($cardId);
    error_log("* DOGMA ARTIFACT $cardName");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->dogmaArtifactOnDisplay();
    $this->tableInstance->advanceGame();

    self::executeInteractions();
  }

  private function returnArtifact()
  {
    error_log("* RETURN ARTIFACT");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->returnArtifactOnDisplay();
    $this->tableInstance->advanceGame();
  }

  private function passArtifact()
  {
    error_log("* PASS ARTIFACT");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->passArtifactOnDisplay();
    $this->tableInstance->advanceGame();
    
    // Return artifacts, if prompted
    self::executeInteractions();
  }

  private function draw()
  {
    error_log("* DRAW");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->draw();
    $this->tableInstance->advanceGame();
  }

  private function meld()
  {
    $cardId = self::getRandomCardId(self::getCards('hand'));
    $cardName = $this->tableInstance->getTable()->getCardName($cardId);
    error_log("* MELD $cardName");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArg('card_id', $cardId)
      ->meld();
    $this->tableInstance->advanceGame();

    // Handle search/dig/steal/junk interactions
    self::executeInteractions();

    if (self::getCurrentStateName() === 'relicPlayerTurn') {
      // TODO(LATER): Seize relic instead of passing
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->passSeizeRelic();
      $this->tableInstance->advanceGame();
    }

    if (self::getCurrentStateName() === 'promoteCardPlayerTurn') {
      $promotedCardId = self::getRandomCardId(self::getCardsToPromote());
      $promotedCardName = $this->tableInstance->getTable()->getCardName($promotedCardId);
      error_log("* PROMOTE $promotedCardName");
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->stubArg('card_id', $promotedCardId)
        ->promoteCard();
      $this->tableInstance->advanceGame();
    }

    if (self::getCurrentStateName() === 'dogmaPromotedPlayerTurn') {
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->dogmaPromotedCard();
      $this->tableInstance->advanceGame();
    }

    self::executeInteractions();
  }

  private function dogma()
  {
    $cardId = self::getRandomCardId(self::getCardsToDogma());
    $cardName = $this->tableInstance->getTable()->getCardName($cardId);
    error_log("* DOGMA $cardName");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArg('card_id', $cardId)
      ->dogma();
    $this->tableInstance->advanceGame();

    self::executeInteractions();
  }

  private function achieveStandardAchievement()
  {
    $value = self::getRandomFromArray(self::getClaimableStandardAchievementValues());
    error_log("* ACHIEVE $value from standard achievements");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArgs(['owner' => 0, 'location' => 'achievements', 'age' => $value])
      ->achieve();
    $this->tableInstance->advanceGame();
  }

  private function achieveSecret()
  {
    $value = self::getRandomFromArray(self::getClaimableSecretValues());
    error_log("* ACHIEVE $value from safe");
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArgs(['owner' => self::getActivePlayerId(), 'location' => 'safe', 'age' => $value])
      ->achieve();
    $this->tableInstance->advanceGame();
  }

}