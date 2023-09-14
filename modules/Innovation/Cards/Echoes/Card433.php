<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card433 extends Card
{

  // Puzzle Cube
  // - 3rd edition 
  //   - You may score the bottom card or two bottom cards of one color from your board. If all
  //     the colors on your board contain the same number of visible cards (unsplayed = 1), you win.
  //   - Draw and meld a [10].
  // - 4th edition
  //   - ECHO: Meld a card from your score pile.
  //   - You may score the bottom card or two bottom cards of one color from your board. If all
  //     colors on your board contain the same number of visible cards, you win.
  //   - Draw and meld a [10].

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(0); // Track number of cards scored
    } else {
      self::drawAndMeld(10);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'score',
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'can_pass'      => true,
        'location_from' => 'board',
        'bottom_from'   => true,
        'score_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      if (self::getNumChosen() === 0 || self::getAuxiliaryValue() === 1) {
        if (self::allColorsHaveSameNumberOfVisibleCards()) {
          self::win();
        }
      } else if (self::getNumChosen() === 1) {
        self::setNextStep(1);
        self::setAuxiliaryValue(1);
      }
    }
  }

  private function allColorsHaveSameNumberOfVisibleCards(): bool
  {
    $counts = [];
    foreach (Colors::ALL as $color) {
      $numVisibleCards = $this->game->countVisibleCards(self::getPlayerId(), $color);
      if ($numVisibleCards > 0) {
        $counts[] = $numVisibleCards;
      }
    }
    return count(array_unique($counts)) <= 1;
  }

}