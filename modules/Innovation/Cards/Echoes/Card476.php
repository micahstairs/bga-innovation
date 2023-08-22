<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card476 extends Card
{

  // Digital Pet
  //   - I DEMAND you draw and reveal an [11]! Return all cards from your board and score pile of
  //     color matching the drawn card!

  public function initialExecution()
  {
    $card = self::transferToHand(self::drawAndReveal(11));
    $this->notifications->notifyCardColor($card['color']);
    self::setAuxiliaryValue($card['color']); // Track color to return
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => 'pile,score',
      'color'          => [self::getAuxiliaryValue()],
      'return_keyword' => true,
    ];
  }

  public function afterInteraction() {
    // Prove that there are no cards of the drawn color left in the score pile
    if (self::countCards('score') > 0) {
      self::revealScorePile();
    }
  }

}