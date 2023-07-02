<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card538 extends Card
{

  // Sniping:
  //   - I DEMAND you unsplay the color on your board of my choice! Meld your bottom card of that
  //     color! Transfer your bottom card of that color to my board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'player_id'    => self::getLauncherId(),
      'choose_color' => true,
    ];
  }

  public function afterInteraction()
  {
      $color = self::getAuxiliaryValue();
      self::unsplay($color);
      $bottomCard = $this->game->getBottomCardOnBoard(self::getPlayerId(), $color);
      if ($bottomCard) {
        self::meld($bottomCard);
      }
      $bottomCard = $this->game->getBottomCardOnBoard(self::getPlayerId(), $color);
      if ($bottomCard) {
        self::transferToBoard($bottomCard, self::getLauncherId());
      }
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

}