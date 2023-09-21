<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card336 extends AbstractCard
{

  // Comb
  // - 3rd edition:
  //   - Choose a color, then draw and reveal five [1]s. Keep all cards that match the color
  //     chosen. Return the rest of the drawn cards.
  // - 4th edition:
  //   - Choose a color, then draw and reveal five [1]. Return the drawn cards that do not match
  //     the chosen color. If Comb was foreseen, return all cards of the chosen color from all
  //     boards.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_color' => true];
    } else if (self::isSecondInteraction()) {
      return [
        'n'              => 'all',
        'location_from'  => 'revealed',
        'return_keyword' => true,
      ];
    } else {
      return [
        'n'              => 'all',
        'owner_from'     => 'any player',
        'location_from'  => 'pile',
        'return_keyword' => true,
        'color'          => [self::getAuxiliaryValue()],
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    $this->notifications->notifyColorChoice($color, self::getPlayerId());
    $revealedCards = [];
    for ($i = 0; $i < 5; $i++) {
      $revealedCards[] = self::drawAndReveal(1);
    }
    foreach ($revealedCards as $card) {
      if ($card['color'] == $color) {
        self::transferToHand($card);
      }
    }
    if (self::wasForeseen()) {
      self::setAuxiliaryValue($color);
      self::setMaxSteps(3);
    }
  }

}