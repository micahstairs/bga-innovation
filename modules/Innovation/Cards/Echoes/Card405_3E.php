<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card405_3E extends AbstractCard
{

  // Radio Telescope (3rd edition):
  //   - For every two [CONCEPT] on your board, draw a [9]. Meld one of the cards drawn and return
  //     the rest. If you meld AI due to this dogma effect, you win.

  public function initialExecution()
  {
    $numCardsToDraw = $this->game->intDivision(self::getStandardIconCount(Icons::CONCEPT), 2);
    if ($numCardsToDraw > 0) {
      $cardIds = [];
      for ($i = 0; $i < $numCardsToDraw; $i++) {
        $card = self::draw(9);
        $cardIds[] = $card['id'];
      }
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