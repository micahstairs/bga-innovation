<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card565 extends Card
{

  // Consulting:
  //   - Choose an opponent. Draw and meld 
  //     two [10]. Self-execute the top 
  //     card on your board of that player's choice.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
      if (self::getCurrentStep() == 1) {
        // "Choose an opponent."
        return [
            'choose_player' => true,
            'players' => $this->game->getActiveOpponents(self::getPlayerId()),
          ];
      } else {
        return [
            'player_id'     => self::getAuxiliaryValue(),
            'location_from' => 'board',
            'location_to'   => 'none', // TODO: THis doesn't work yet because there isn't an entry for this card in the board->none location
          ];          
      }
  }


  public function afterInteraction()
  {
      if (self::getCurrentStep() == 2) {
        if (self::getNumChosen() > 0) {
          // "Self-execute the top card on your board of that player's choice."
          self::selfExecute(self::getLastSelectedCard());
        }
      }
  }  
  
  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must choose another player to choose a card to self-execute:'),
      "message_for_others" => clienttranslate('${player_name} must choose another player to choose a card to self-execute'),
      ];
  }

  public function handleSpecialChoice(int $choice): void
  {
      if (self::getCurrentStep() == 1) {
        $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} choose the player ${player_choice}.'), 
            array('You' => 'You', 
            'player_choice' => $this->game->getColoredPlayerName($choice)));
        $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} chooses the player ${player_choice}.'), 
            array('player_name' => $this->game->getColoredPlayerName(self::getPlayerId()),
            'player_choice' => $this->game->getColoredPlayerName($choice)));
        self::setAuxiliaryValue($choice);
        
        // "Draw and meld two [10].
        self::drawAndMeld(10);
        self::drawAndMeld(10);
      }
  }
  
}