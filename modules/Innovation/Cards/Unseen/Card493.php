<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card493 extends AbstractCard
{

  // Polytheism:
  //   - Meld a card from your hand with no icon on a card already melded by you during this action
  //     due to Polytheism. If you do, repeat this effect.
  //   - Draw and tuck a [1].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::drawAndTuck(1);
    }
  }

  public function getInteractionOptions(): array
  {
    $iconsMelded = self::getActionScopedAuxiliaryArray(self::getPlayerId());
    $cardIds = [];
    $cardsInHand = self::getCards(Locations::HAND);
    foreach ($cardsInHand as $card) {
      foreach ($iconsMelded as $icon) {
        if (self::hasIcon($card, $icon)) {
          continue 2;
        }
      }
      $cardIds[] = $card['id'];
    }
    self::setAuxiliaryArray($cardIds);
    return [
      'location_from'                   => Locations::HAND,
      'meld_keyword'                    => true,
      'card_ids_are_in_auxiliary_array' => true,
      // Automating this can sometimes reveal hidden info
      'enable_autoselection'            => count($cardsInHand) <= 1,
      'reveal_if_unable'                => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    $iconsMelded = array_unique(array_merge(self::getActionScopedAuxiliaryArray(self::getPlayerId()), self::getIcons($card)));
    self::setActionScopedAuxiliaryArray($iconsMelded, self::getPlayerId());
    self::setNextStep(1);
  }

}