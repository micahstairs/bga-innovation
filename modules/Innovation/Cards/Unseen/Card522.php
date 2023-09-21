<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card522 extends AbstractCard
{

  // Heirloom:
  //   - Transfer one of your secrets to the available achievements and draw a card of value one
  //     higher than the transferred card. If you don't, safeguard an available achievement of
  //     value equal to the value of your top red card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'safe',
        'location_to'   => Locations::AVAILABLE_ACHIEVEMENTS,
      ];
    } else {
      $topRedCard = self::getTopCardOfColor(Colors::RED);
      $value = $topRedCard ? $topRedCard['faceup_age'] : 0;
      return [
        'safeguard_keyword' => true,
        'age'               => $value,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $valueToDraw = self::getNumChosen() === 1 ? self::getLastSelectedAge() + 1 : 1;
      $card = self::draw($valueToDraw);
      // "If you don't" happens whenever you either are unable to transfer a secret or you draw a
      // card of a different value than one higher of the transferred secret.
      if (self::getNumChosen() === 0 || $card['age'] != $valueToDraw) {
        self::setMaxSteps(2);
      }
    }
  }

}