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

  public function afterInteraction(ExecutionState $state)
  {
    // Subclasses are expected to override this method if the card has any interactions.
  }

  // HELPER METHODS

  protected function getPromptForIconChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose an icon'),
      "message_for_others" => clienttranslate('${player_name} must choose an icon'),
    ];
  }

}