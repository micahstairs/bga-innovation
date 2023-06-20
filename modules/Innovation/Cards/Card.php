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

  public abstract function initialExecution(ExecutionState $state);

  public function getInteractionOptions(ExecutionState $state): array
  {
    // Subclasses are expected to override this method if the card has any interactions.
    return [];
  }

  public function getSpecialChoicePrompt(ExecutionState $state): array
  {
    switch ($this->game->innovationGameState->get('special_type_of_choice')) {
      case 4: // choose_color
        return $this->getPromptForColorChoice();
      case 12: // choose_icon_type
        return $this->getPromptForIconChoice();
      default:
        return [];
    }
  }

  public function handleSpecialChoice(ExecutionState $state, int $choice)
  {
    // Subclasses are expected to override this method if the card has any special choices.
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
    // Subclasses can optionally override this method if any extra handling is needed after individual cards are chosen.
  }

  public function afterInteraction(ExecutionState $state)
  {
    // Subclasses can optionally override this method if any extra handling needs to be done after an entire interaction is complete.
  }

  // CARD HELPERS

  protected function draw(int $age, int $playerId = null)
  {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->executeDraw($playerId, $age);
  }

  protected function drawAndMeld(int $age, int $playerId = null)
  {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->executeDrawAndMeld($playerId, $age);
  }

  protected function drawAndTuck(int $age, int $playerId = null)
  {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->executeDrawAndTuck($playerId, $age);
  }

  protected function drawAndScore(int $age, int $playerId = null)
  {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->executeDrawAndScore($playerId, $age);
  }

  protected function drawAndSafeguard(int $age, int $playerId = null)
  {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->executeDrawAndSafeguard($playerId, $age);
  }

  protected function drawAndReveal(int $age, int $playerId = null)
  {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->executeDrawAndReveal($playerId, $age);
  }

  protected function putInHand($card, int $playerId = null) {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    return $this->game->transferCardFromTo($card, $playerId, 'hand');
  }

  // SPLAY HELPERS

  protected function unsplay(int $color, int $playerId = null) {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    $this->game->unsplay($playerId, $playerId, $color);
  }

  protected function splayLeft(int $color, int $playerId = null) {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    $this->game->splayLeft($playerId, $playerId, $color);
  }
  
  protected function splayRight(int $color, int $playerId = null) {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    $this->game->splayRight($playerId, $playerId, $color);
  }

  protected function splayUp(int $color, int $playerId = null) {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    $this->game->splayUp($playerId, $playerId, $color);
  }

  protected function splayAslant(int $color, int $playerId = null) {
    if ($playerId === null) {
      $playerId = $this->state->getPlayerId();
    }
    $this->game->splayAslant($playerId, $playerId, $color);
  }

  // SELECTION HELPERS

  protected function getLastSelectedCard() {
    return $this->game->getCardInfo(self::getLastSelectedId());
  }

  protected function getLastSelectedId(): int {
    return $this->game->innovationGameState->get('id_last_selected');
  }

  protected function getLastSelectedAge(): int {
    return $this->game->innovationGameState->get('age_last_selected');
  }

  protected function getLastSelectedColor(): int {
    return $this->game->innovationGameState->get('color_last_selected');
  }

  // AUXILARY VALUE HELPERS

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
      "message_for_player" => clienttranslate('${You} must choose a color'),
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

  // GENERAL UTILITY HELPERS

  protected function getCardIdFromClassName(): string
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5));
  }

}