<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card405_4E extends AbstractCard
{

  // Radio Telescope (4th edition):
  //   - For every color on your board with [CONCEPT], draw a [9], and if Radio Telescope was
  //     foreseen, draw a [10]. Meld one of the drawn cards and return the rest. If you meld AI due
  //     to this effect, you win.

  public function initialExecution()
  {
    $cardIds = [];
    foreach (Colors::ALL as $color) {
      if (self::getIconCountInStack($color, Icons::CONCEPT) > 0) {
        $card = self::draw(9);
        $cardIds[] = $card['id'];
      }
    }
    if (self::wasForeseen()) {
      $card = self::draw(10);
      $cardIds[] = $card['id'];
    }
    if ($cardIds) {
      self::setAuxiliaryArray($cardIds);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from'                   => Locations::HAND,
        'meld_keyword'                    => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'                               => count(self::getAuxiliaryArray()),
        'location_from'                   => Locations::HAND,
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      if ($card['id'] == CardIds::AI) {
        self::win();
      } else {
        self::removeFromAuxiliaryArray($card['id']);
      }
    }
  }

}