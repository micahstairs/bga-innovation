<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card568 extends Card
{

  // McCarthyism:
  //   - I demand you draw and meld an [8]! If Socialism is a top card on your board, you lose!
  //   - Score your top purple card.
  //   - You may splay your red or blue cards up.
  
  public function initialExecution()
  {
      if (self::isDemand()) {
        // "I demand you draw and meld an [8]!"
        self::drawAndMeld(8);
        $top_purple_card = $this->game->getTopCardOnBoard(self::getPlayerId(), 4); // top purple card
        if ($top_purple_card !== null && $top_purple_card['id'] == 84) {
            // "If Socialism is a top card on your board, you lose!"
            // TODO: the game type doesn't appear to be accessible from this class.
            if ($this->game->decodeGameType($this->get('game_type')) == 'individual') {
                $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('Socialism is your top card.  ${You} lose.'),  array('You' => 'You'));
                $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('Socialism is their top card.  ${player_name} loses.'), array(
                    'player_name' => $this->game->getColoredPlayerName(self::getPlayerId())
                ));
                if (count($this->game->getAllActivePlayers()) == 2) {
                    $this->innovationGameState->set('winner_by_dogma', self::getLauncherId());
                    $this->game->trace('EOG bubbled from initialExecution Exxon Valdez');
                    throw new EndOfGame();
                } else {
                    // Only eliminate the player if the game isn't ending
                    $this->game->eliminatePlayer(self::getPlayerId());
                }
            } else { // Team play
                // Entire team loses if one player loses 
                $teammate_id = $this->game->getPlayerTeammate(self::getPlayerId());
                $losing_team = array(self::getPlayerId(), $teammate_id);
                $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('Socialism is your top card.  ${Your} team loses.'), array('Your' => 'Your'));
                $this->game->notifyPlayer($teammate_id, 'log', clienttranslate('Socialism is a teammates top card.  ${Your} team loses.'), array('Your' => 'Your'));
                $this->game->notifyAllPlayersBut($losing_team, 'log', clienttranslate('The other team loses.'), array());
                $this->state->set('winner_by_dogma', self::getLauncherId());
                $this->game->trace('EOG bubbled from self::stInterInteractionStep McCarthyism');
                throw new EndOfGame();
            }
        }
      } else if (self::getEffectNumber() == 1) {
          // "Score your top purple card."
          $top_purple_card = $this->game->getTopCardOnBoard(self::getPlayerId(), 4);
          if ($top_purple_card !== null) {
              self::score($top_purple_card);
          }
      } else {
          self::setMaxSteps(1);
      }
  }

  public function getInteractionOptions(): array
  {
    
    if (self::getEffectNumber() == 2) {
        // "You may splay your red or blue cards up."
        return [
          'can_pass'        => true,
          'splay_direction' => $this->game::UP,
          'color'           => array($this->game::RED, $this->game::BLUE),
        ];
    } 
  }
}