<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card585 extends Card
{

  // Astrobiology:
  //   - Return a bottom card from your board. Splay that color on your board aslant. Score all
  //     cards on your board of that color without a [HEALTH].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'board',
      'bottom_from'   => true,
      'location_to'   => 'deck',
    ];
  }

  public function handleCardChoice($cardId)
  {
    $color = self::getLastSelectedColor();
    self::splayAslant($color);
    foreach ($this->game->getCardsInLocationKeyedByColor(self::getPlayerId(), 'board')[$color] as $card) {
      if (!$this->game->hasRessource($card, $this->game::HEALTH)) {
        self::score($card);
      }
    }
  }

}