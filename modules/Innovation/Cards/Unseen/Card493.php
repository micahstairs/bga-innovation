<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card493 extends Card
{

  // Polytheism:
  //   - Meld a card from your hand with no icon on a card already melded by you during this action
  //     due to Polytheism. If you do, repeat this effect. Otherwise, draw and tuck a [1].

  public function initialExecution(ExecutionState $state)
  {
    $state->setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    $iconsMelded = self::getActionScopedAuxiliaryArray($state->getPlayerId());
    $cardIds = [];
    foreach ($this->game->getCardsInHand($state->getPlayerId()) as $card) {
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

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getNumChosen() == 1) {
      $card = self::getLastSelectedCard();
      $iconsMelded = array_unique(array_merge(self::getActionScopedAuxiliaryArray($state->getPlayerId()), self::getIcons($card)));
      self::setActionScopedAuxiliaryArray($iconsMelded, $state->getPlayerId());
      $state->setNextStep(1);
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