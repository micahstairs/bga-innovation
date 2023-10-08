<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card495 extends AbstractCard
{
  // Astrology
  //   - You may splay left the color of which you have the most cards on your board.
  //   - Draw and meld a card of value equal to the number of visible purple cards on your board.
  //     If the melded card has no [PROSPERITY], tuck it.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      $numVisiblePurple = self::countVisibleCardsInStack(Colors::PURPLE);
      $card = self::drawAndMeld($numVisiblePurple);
      if (!self::hasIcon($card, Icons::PROSPERITY)) {
        self::tuck($card);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    $stackSizes = self::countCardsKeyedByColor(Locations::BOARD);
    $maxStackSize = max($stackSizes);
    $colors = [];
    foreach (Colors::ALL as $color) {
      if ($stackSizes[$color] == $maxStackSize) {
        $colors[] = $color;
      }
    }
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::LEFT,
      'color'           => $colors,
    ];
  }

}