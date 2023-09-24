<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card187 extends AbstractCard
{
  // Battleship Bismarck
  //   - I COMPEL you to draw and reveal an [8]! Return all cards of the drawn card's color from
  //     your board!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $card = self::drawAndReveal(8);
    self::transferToHand($card);
    return [
      'n'              => 'all',
      'location_from'  => 'pile',
      'color'          => [$card['color']],
      'return_keyword' => true,
    ];
  }

}