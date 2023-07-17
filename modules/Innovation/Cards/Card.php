<?php

namespace Innovation\Cards;

use Innovation\Cards\ExecutionState;
use Innovation\Utils\Notifications;

/* Abstract class of all card implementations */
abstract class Card
{

  protected \Innovation $game;
  protected ExecutionState $state;
  protected Notifications $notifications;

  function __construct(\Innovation $game, ExecutionState $state)
  {
    $this->game = $game;
    $this->state = $state;
    $this->notifications = $game->notifications;
  }
  
  public function oneTimeSetup()
  {
    // Subclasses are expected to override this method if the card need to do any one-time setup before any player executes anything.
    // TODO(LATER): This method isn't actually called from the game logic yet. We need to wire it up if we want to use this method.
  }

  public abstract function initialExecution();

  public function getInteractionOptions(): array
  {
    // Subclasses are expected to override this method if the card has any interactions.
    return [];
  }

  public function hasPostExecutionLogic(): bool
  {
    // Subclasses are expected to override this method and return true if the card needs to do any more logic after executing a card.
    return false;
  }

  public function getSpecialChoicePrompt(): array
  {
    switch ($this->game->innovationGameState->get('special_type_of_choice')) {
      case 3: // choose_value
        return self::getPromptForValueChoice();
      case 4: // choose_color
        return self::getPromptForColorChoice();
      case 10: // choose_player
        return self::getPromptForPlayerChoice();
      case 12: // choose_icon_type
        return self::getPromptForIconChoice();
      default:
        return [];
    }
  }

  public function handleSpecialChoice(int $choice)
  {
    // Subclasses are expected to override this method if the card has any special choices.
  }

  public function handleCardChoice(array $card)
  {
    // Subclasses can optionally override this method if any extra handling is needed after individual cards are chosen.
  }

  public function afterInteraction()
  {
    // Subclasses can optionally override this method if any extra handling needs to be done after an entire interaction is complete.
  }

  // EXECUTION HELPERS

  protected function isFirstOrThirdEdition(): bool
  {
    return $this->state->getEdition() <= 3;
  }

  protected function isFourthEdition(): bool
  {
    return $this->state->getEdition() === 4;
  }

  protected function getPlayerId(): int
  {
    return $this->state->getPlayerId();
  }

  protected function getLauncherId(): int
  {
    return $this->state->getLauncherId();
  }

  protected function getEffectNumber(): int
  {
    return $this->state->getEffectNumber();
  }

  protected function isDemand(): bool
  {
    return $this->state->isDemand();
  }

  protected function isNonDemand(): bool
  {
    return $this->state->isNonDemand();
  }

  protected function isEcho(): bool
  {
    return $this->state->isEcho();
  }

  protected function getCurrentStep(): int
  {
    return $this->state->getCurrentStep();
  }

  protected function setNextStep(int $step)
  {
    $this->state->setNextStep($step);
  }

  protected function setMaxSteps(int $steps)
  {
    $this->state->setMaxSteps($steps);
  }

  protected function getNumChosen(): int
  {
    return $this->state->getNumChosen();
  }

  protected function getPostExecutionIndex(): int
  {
    return $this->game->getCurrentNestedCardState()['post_execution_index'];
  }

  protected function setPostExecutionIndex(int $index)
  {
    $this->game->updateCurrentNestedCardState('post_execution_index', $index);
  }

  protected function selfExecute($card): bool
  {
    if (!$card) {
      return false;
    }
    return $this->game->selfExecute($card);
  }

  protected function fullyExecute($card)
  {
    if ($card) {
      $this->game->fullyExecute($card);
    }
  }

  // CARD TRANSFER HELPERS

  protected function draw(int $age, int $playerId = null)
  {
    return $this->game->executeDraw(self::coercePlayerId($playerId), $age);
  }

  protected function score($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->scoreCard($card, self::coercePlayerId($playerId));
  }

  protected function transferToScorePile($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'score');
  }

  protected function meld($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->meldCard($card, self::coercePlayerId($playerId));
  }

  protected function transferToBoard($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'board');
  }

  protected function tuck($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->tuckCard($card, self::coercePlayerId($playerId));
  }

  protected function reveal($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'revealed');
  }

  protected function safeguard($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->safeguardCard($card, self::coercePlayerId($playerId));
  }

  protected function putBackInSafe($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->putCardBackInSafe($card, self::coercePlayerId($playerId));
  }

  protected function achieve($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), "achievements");
  }

  protected function return($card)
  {
    if (!$card) {
      return null;
    }
    return $this->game->returnCard($card);
  }

  protected function placeOnTopOfDeck($card)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, 0, 'deck', /*bottom_to=*/ false);
  }

  protected function junk($card)
  {
    if (!$card) {
      return null;
    }
    return $this->game->junkCard($card);
  }

  protected function putInHand($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'hand');
  }

  protected function drawAndMeld(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndMeld(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndTuck(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndTuck(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndScore(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndScore(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndSafeguard(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndSafeguard(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndForeshadow(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndForeshadow(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndReveal(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndReveal(self::coercePlayerId($playerId), $age);
  }

  protected function getTopCardOfColor(int $color, int $playerId = null)
  {
    return $this->game->getTopCardOnBoard(self::coercePlayerId($playerId), $color);
  }

  protected function getBottomCardOfColor(int $color, int $playerId = null)
  {
    return $this->game->getBottomCardOnBoard(self::coercePlayerId($playerId), $color);
  }

  // CARD ACCESSOR HELPERS

  protected function getMinValueInLocation(string $location, int $playerId = null): int
  {
    return $this->game->getMinOrMaxAgeInLocation(self::coercePlayerId($playerId), $location, 'MIN');
  }

  protected function getMaxValueInLocation(string $location, int $playerId = null): int
  {
    return $this->game->getMinOrMaxAgeInLocation(self::coercePlayerId($playerId), $location, 'MAX');
  }

  protected function hasIcon($card, int $icon): bool
  {
    return $this->game->hasRessource($card, $icon);
  }

  protected function isValuedCard($card): bool
  {
    return $card['age'] !== null;
  }

  protected function isSpecialAchievement($card): bool
  {
    return $card['age'] === null;
  }

  protected function getCard(int $cardId)
  {
    return $this->game->getCardInfo($cardId);
  }

  // SPLAY HELPERS

  protected function unsplay(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null)
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    $this->game->unsplay($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function splayLeft(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayLeft($playerId, $playerId, $color);
  }

  protected function splayRight(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayRight($playerId, $playerId, $color);
  }

  protected function splayUp(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayUp($playerId, $playerId, $color);
  }

  protected function splayAslant(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayAslant($playerId, $playerId, $color);
  }

  protected function getSplayDirection(int $color, int $playerId = null): int
  {
    return $this->game->getCurrentSplayDirection(self::coercePlayerId($playerId), $color);
  }

  // COLOR HELPERS

  protected function getAllColorsOtherThan(int $color)
  {
    return array_diff(range(0, 4), [$color]);
  }

  // SELECTION HELPERS

  protected function getLastSelectedCard()
  {
    return $this->game->getCardInfo(self::getLastSelectedId());
  }

  protected function getLastSelectedId(): int
  {
    return $this->game->innovationGameState->get('id_last_selected');
  }

  protected function getLastSelectedAge(): int
  {
    return $this->game->innovationGameState->get('age_last_selected');
  }

  protected function getLastSelectedColor(): int
  {
    return $this->game->innovationGameState->get('color_last_selected');
  }

  // AUXILARY VALUE HELPERS

  protected function getAuxiliaryValue(): int
  {
    return $this->game->getAuxiliaryValue();
  }

  protected function setAuxiliaryValue(int $value)
  {
    return $this->game->setAuxiliaryValue($value);
  }

  protected function incrementAuxiliaryValue(int $value = 1)
  {
    return self::setAuxiliaryValue(self::getAuxiliaryValue() + $value);
  }

  protected function getAuxiliaryValue2(): int
  {
    return $this->game->getAuxiliaryValue2();
  }

  protected function setAuxiliaryValue2(int $value)
  {
    return $this->game->setAuxiliaryValue2($value);
  }

  protected function setAuxiliaryArray(array $array)
  {
    return $this->game->setAuxiliaryArray($array);
  }

  protected function getAuxiliaryArray(): array
  {
    return $this->game->getAuxiliaryArray();
  }

  protected function setActionScopedAuxiliaryArray($array, $playerId = 0): void
  {
    $this->game->setActionScopedAuxiliaryArray(self::getCardIdFromClassName(), $playerId, $array);
  }

  protected function getActionScopedAuxiliaryArray($playerId = 0): array
  {
    return $this->game->getActionScopedAuxiliaryArray(self::getCardIdFromClassName(), $playerId);
  }

  // PROMPT MESSAGE HELPERS

  protected function getPromptForColorChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose a color'),
      "message_for_others" => clienttranslate('${player_name} must choose a color'),
    ];
  }

  protected function getPromptForIconChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose an icon'),
      "message_for_others" => clienttranslate('${player_name} must choose an icon'),
    ];
  }

  protected function getPromptForValueChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose a value'),
      "message_for_others" => clienttranslate('${player_name} must choose a value'),
    ];
  }

  protected function getPromptForPlayerChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose a player'),
      "message_for_others" => clienttranslate('${player_name} must choose a player'),
    ];
  }

  protected function getPromptForChoiceFromList(array $choiceMap): array
  {
    $options = self::getOptionsForChoiceFromList($choiceMap);
    if ($this->game->innovationGameState->get('can_pass')) {
      return [
        "message_for_player" => clienttranslate('${You} may make a choice'),
        "message_for_others" => clienttranslate('${player_name} may make a choice among the possibilities offered by the card'),
        "options"            => $options,
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('${You} must make a choice'),
        "message_for_others" => clienttranslate('${player_name} must make a choice among the possibilities offered by the card'),
        "options"            => $options,
      ];
    }
  }

  protected function getOptionsForChoiceFromList(array $choiceMap): array
  {
    $validChoices = $this->game->innovationGameState->getAsArray('choice_array');
    $options = [];
    foreach ($choiceMap as $value => $textOrarray) {
      if (!in_array($value, $validChoices)) {
        continue;
      }
      $text = is_array($textOrarray) ? reset($textOrarray) : $textOrarray;
      $args = is_array($textOrarray) ? array_slice($textOrarray, 1) : [];
      $options[] = array_merge($args, [
        'value' => $value,
        'text'  => $text,
      ]);
    }
    return $options;
  }

  // WINNING AND LOSING HELPERS

  protected function win(int $playerId = null): void
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->innovationGameState->set('winner_by_dogma', $playerId);
    throw new \EndOfGame();
  }

  protected function lose(int $playerId = null): void
  {
    $playerId = self::coercePlayerId($playerId);

    // The entire team loses if one player loses 
    if ($this->game->isTeamGame()) {
      $teammateId = $this->game->getPlayerTeammate($playerId);
      $this->notifications->notifyTeamLoses($playerId, $teammateId);
      $arbitraryOpponentId = self::getRemainingPlayerIdsAfterEliminating([$playerId, $teammateId])[0];
      $this->game->innovationGameState->set('winner_by_dogma', $arbitraryOpponentId);
      throw new \EndOfGame();
    }

    // Declare a winner if there will only be one player remaining
    $remainingPlayerIds = self::getRemainingPlayerIdsAfterEliminating([$playerId]);
    if (count($remainingPlayerIds) === 1) {
      $this->notifications->notifyPlayerLoses($playerId);
      $this->game->innovationGameState->set('winner_by_dogma', $remainingPlayerIds[0]);
      throw new \EndOfGame();
    }

    // Otherwise, eliminate the player
    $this->notifications->notifyPlayerLoses($playerId);
    $this->game->eliminatePlayer($playerId);
  }

  private function getRemainingPlayerIdsAfterEliminating(array $idsToEliminate): array
  {
    return array_values(array_diff($this->game->getAllActivePlayerIds(), $idsToEliminate));
  }

  // MISCELLANEOUS HELPERS

  protected function getScore(int $playerId = null): int
  {
    return $this->game->getPlayerScore(self::coercePlayerId($playerId));
  }

  protected function wasForeseen(): bool
  {
    return $this->game->innovationGameState->get('foreseen_card_id') == self::getCardIdFromClassName();
  }

  // GENERAL UTILITY HELPERS

  protected function getCardIdFromClassName(): string
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5));
  }

  // PRIVATE HELPERS

  private function coercePlayerId(?int $playerId): int
  {
    if ($playerId === null) {
      return $this->state->getPlayerId();
    }
    return $playerId;
  }

}