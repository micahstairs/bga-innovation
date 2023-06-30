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
    foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
      foreach ($iconsMelded as $icon) {
        if ($this->game->hasRessource($card, $icon)) {
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
    if (self::getNumChosen() == 1) {
      $card = self::getLastSelectedCard();
      $iconsMelded = array_unique(array_merge(self::getActionScopedAuxiliaryArray(self::getPlayerId()), self::getIcons($card)));
      self::setActionScopedAuxiliaryArray($iconsMelded, self::getPlayerId());
      self::setNextStep(1);
    } else {
      self::drawAndTuck(1);
    }
  }

  private function getIcons($card): array
  {
    $icons = [];
    for ($i = 1; $i <= 6; $i++) {
      $icon = $card['spot_' . $i];
      if ($icon > 0) {
        // TODO(4E): There is currently a bug here with Search icons.
        $icons[] = $icon;
      }
    }
    return $icons;
  }
}