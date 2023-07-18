<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card536 extends Card
{

  // Reconnaissance:
  //   - I DEMAND you reveal your hand!
  //   - Draw and reveal three [7]. Return two of the drawn cards.  You may splay the color of the
  //     card not returned right.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::revealHand();
    } else {
      $cardIds = array();
      for ($i = 0; $i < 3; $i++) {
        $card = self::drawAndReveal(7);
        $cardIds[] = $card['id'];
      }
      $this->game->setAuxiliaryArray($cardIds);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'n'                               => 2,
        'location_from'                   => 'revealed',
        'location_to'                     => 'deck',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => array(self::getAuxiliaryValue()),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
      $revealedCard = $this->game->getCardsInLocation(self::getPlayerId(), 'revealed')[0];
      self::putInHand($revealedCard);
      self::setAuxiliaryValue($revealedCard['color']);
    }
  }

}