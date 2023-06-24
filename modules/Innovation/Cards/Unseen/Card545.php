<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card545 extends Card
{

  // Counterintelligence:
  //   - I demand you tuck a top card on your board with 
  //     a CONCEPT! If you do, transfer your top card of 
  //     color matching the tucked card to my board, and draw a [7]!
  //   - Draw an [8].

  public function initialExecution()
  {
    if (self::isDemand()) {
        self::setMaxSteps(1);
    } else {
        // "Draw an [8]"
        self::draw(8);
    }
  }

  public function getInteractionOptions(): array
  {
      if (self::isDemand()) {
        // "I demand you tuck a top card on your board with a CONCEPT!"
        return [
          'can_pass'      => false,
          'location_from' => 'board',
          'location_to'   => 'board',
          'bottom_to'     => true,
          'with_icon'     => $this->game::CONCEPT,
        ];
      }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
        if (self::getNumChosen() > 0) { // "If you do,"
            // " transfer your top card of color matching the tucked card to my board,"
            $top_card = $this->game->getTopCardOnBoard(self::getPlayerId(), $this->game->innovationGameState->get('color_last_selected'));
            if ($top_card !== null) {
                $this->game->transferCardFromTo($top_card, self::getLauncherId(), 'board');
            }
            // "and draw a [7]!"
            self::draw(7);
        }
    } 
  }

}