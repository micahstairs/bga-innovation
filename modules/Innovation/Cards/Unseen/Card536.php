<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;

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
    if (self::isFirstInteraction()) {
      return [
        'n'                               => 2,
        'location_from'                   => 'revealed',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => array(self::getAuxiliaryValue()),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $revealedCard = self::getRevealedCard();
      self::transferToHand($revealedCard);
      self::setAuxiliaryValue($revealedCard['color']);
    }
  }

}