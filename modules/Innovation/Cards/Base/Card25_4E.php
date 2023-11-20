<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card25_4E extends AbstractCard
{
  // Alchemy (4th edition):
  //   - Draw and reveal a [4] for every color on your board with [AUTHORITY]. If any of the drawn
  //     cards are red, return all cards from your hand.
  //   - Meld a card from your hand, then score a card from your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $numCardsToDraw = 0;
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::AUTHORITY) > 0) {
          $numCardsToDraw++;
        }
      }
      $drewRed = false;
      for ($i = 0; $i < $numCardsToDraw; $i++) {
        $card = self::transferToHand(self::drawAndReveal(4));
        if (self::isRed($card)) {
          $drewRed = true;
        }
      }
      if ($drewRed) {
        self::notifyPlayer(clienttranslate('${You} drew a red card.'));
        self::notifyOthers(clienttranslate('${player_name} drew a red card.'));
        self::setMaxSteps(1);
      } else {
        self::notifyPlayer(clienttranslate('${You} did not draw a red card.'));
        self::notifyOthers(clienttranslate('${player_name} did not draw a red card.'));
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'score_keyword' => true
      ];
    }
  }

}