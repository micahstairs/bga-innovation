<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card493 extends Card
{

  // Polytheism:
  //   - Meld a card from your hand with no icon on a card already melded by you during this action
  //     due to Polytheism. If you do, repeat this effect. Otherwise, draw and tuck a [1].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $iconsMelded = self::getActionScopedAuxiliaryArray(self::getPlayerId());
    $cardIds = [];
    foreach (self::getCards('hand') as $card) {
      foreach ($iconsMelded as $icon) {
        if (self::hasIcon($card, $icon)) {
          continue 2;
        }
      }
      $cardIds[] = $card['id'];
    }
    $this->game->setAuxiliaryArray($cardIds);
    return [
      'location_from'                   => 'hand',
      'meld_keyword'                    => true,
      'card_ids_are_in_auxiliary_array' => true,
      'enable_autoselection'            => false, // Automating this can sometimes reveal hidden info
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 1) {
      $card = self::getLastSelectedCard();
      $iconsMelded = array_unique(array_merge(self::getActionScopedAuxiliaryArray(self::getPlayerId()), self::getIcons($card)));
      self::setActionScopedAuxiliaryArray($iconsMelded, self::getPlayerId());
      self::setNextStep(1);
    } else {
      self::drawAndTuck(1);
    }
  }

}