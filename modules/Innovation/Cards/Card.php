<?php

namespace Innovation\Cards;

use Innovation\Cards\ExecutionState;
use Innovation\Utils\Notifications;

/* Abstract class of all card implementations */
abstract class Card
{

  protected \Innovation $game;
  protected Notifications $notifications;

  function __construct(\Innovation $game)
  {
    $this->game = $game;
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

  protected function drawAndTuck(int $player_id, int $age)
  {
    return $this->game->executeDrawAndTuck($player_id, $age);
  }

  // AUXILARY VALUE HELPERS

  protected function setActionScopedAuxiliaryArray($array, $player_id = 0): void
  {
    $this->game->setActionScopedAuxiliaryArray(self::getCardIdFromClassName(), $player_id, $array);
  }

  protected function getActionScopedAuxiliaryArray($player_id = 0): array
  {
    return $this->game->getActionScopedAuxiliaryArray(self::getCardIdFromClassName(), $player_id);
  }

  // PROMPT MESSAGE HELPERS

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