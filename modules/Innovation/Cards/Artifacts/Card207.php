<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card207 extends AbstractCard
{
  // Exxon Valdez (3rd edition):
  //   - I COMPEL you to remove all cards from your hand, score pile, board, and achievements from
  //     the game! You lose! If there is only one player remaining in the game, that player wins!
  // Tanker Exxon Valdez (4th edition):
  //   - I COMPEL you to junk all your cards! You lose!

  public function initialExecution()
  {
    // NOTE: It's technically possible for the player who is about to lose the game to achieve the
    // Glory and/or Victory special achievements and win the game.
    self::junkAllCards();
    self::lose();
  }

}