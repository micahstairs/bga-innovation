<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card574 extends Card
{

  // Urban Legend:
  //   - For each color on your board with a INDUSTRY, draw a [9]. If you draw five cards, you win.
  //   - You may splay your yellow or purple cards up.

  public function initialExecution()
  {
    if (self::getEffectNumber() == 1) {
      $cards_drawn_ctr = 0;
        for ($color = 0; $color < 5; $color++) {
            if ($this->game->countVisibleIconsInPile(self::getPlayerId(), $this->game::INDUSTRY, $color) > 0) {
                // For each color on your board with a INDUSTRY, draw a [9].
                self::draw(9);
                $cards_drawn_ctr++;
            }
        }
        
        if ($cards_drawn_ctr == 5) {
            // TODO: verify that innovationGameState is correct
            $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} have 5 colors with an ${icon_5}.'), array('You' => 'You'));
            $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} has 5 colors with an ${icon_5}.'), array('player_name' => $this->game->getColoredPlayerName(self::getPlayerId())));
            $this->innovationGameState->set('winner_by_dogma', self::getPlayerId());
            $this->game->trace('EOG bubbled from self::stPlayerInvolvedTurn Urban Legend');
            throw new EndOfGame();
        }
    } else {
        self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => $this->game::UP,
      'color'           => array($this->game::YELLOW, $this->game::PURPLE),
    ];
  }
  
}