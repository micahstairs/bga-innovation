<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card475 extends Card
{

  // Robocar
  //   - Choose an opponent. That player chooses a card (unrevealed) in your hand. Meld the chosen
  //     card. If you do, and it is your turn, self-execute the card, then repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_player' => true,
        'players' => $this->game->getActiveOpponents(self::getPlayerId()),
      ];
    } else {
      return [
        'player_id' => self::getAuxiliaryValue(),
        'location_from' => 'hand',
        'owner_from' => self::getPlayerId(),
        'location_to' => 'board',
        'owner_to' => self::getPlayerId(),
        'meld_keyword' => true,
      ];
    }
  }

  public function handleSpecialChoice(int $player)
  {
    self::setAuxiliaryValue($player); // Track opponent chosen
  }

  public function handleCardChoice(array $card) {
    if (self::isLauncher()) {
      self::selfExecute($card);
      // TODO(4E): Handle "repeat this effect" after confirming with Carl that this card will not change.
    }
  }

}