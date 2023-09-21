<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card162_3E extends AbstractCard
{
  // The Daily Courant (3rd edition):
  //   - Draw a card of any value, then place it on top of the draw pile of its age. You may
  //     execute the effects of one of your other top cards as if they were on this card. Do not
  //     share them.

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_value' => true];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from'                   => Locations::HAND,
        'topdeck_keyword'                 => true,
        'card_ids_are_in_auxiliary_array' => true,
        'enable_autoselection'            => false, // Give the player the chance to read the card
      ];
    } else {
      return [
        'can_pass'    => true,
        'choose_from' => 'board',
        // Exclude the card currently being executed (it's possible for the effects of The Daily Courant to be executed as if it were on another card)
        'not_id'      => $this->game->getCurrentNestedCardState()['executing_as_if_on_card_id'],
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    $card = self::draw($value);
    self::setAuxiliaryArray([$card['id']]);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isThirdInteraction()) {
      self::fullyExecute($card);
    }
  }

}