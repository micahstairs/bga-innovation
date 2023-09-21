<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card565 extends AbstractCard
{

  // Consulting:
  //   - Choose an opponent. Draw and meld two [10]. Self-execute the top card on your board of
  //     that player's choice.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_player' => true,
        'players'       => $this->game->getActiveOpponents(self::getPlayerId()),
      ];
    } else {
      return [
        'player_id'   => self::getAuxiliaryValue(),
        'choose_from' => 'board',
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

  public function handlePlayerChoice(int $playerId): void
  {
    $this->notifications->notifyPlayerChoice($playerId, self::getPlayerId());
    self::setAuxiliaryValue($playerId);
    self::drawAndMeld(10);
    self::drawAndMeld(10);
  }

}