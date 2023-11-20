<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card25_3E extends AbstractCard
{
  // Alchemy (3rd edition):
  //   - Draw and reveal a [4] for every three [AUTHORITY] on your board. If any of the drawn cards
  //     are red, return the cards drawn and all cards in your hand. Otherwise, keep them.
  //   - Meld a card from your hand, then score a card from your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $numCardsToDraw = $this->game->intDivision(self::getStandardIconCount(Icons::AUTHORITY), 3);
      $drewRed = false;
      for ($i = 0; $i < $numCardsToDraw; $i++) {
        if (self::isRed(self::draw(4))) {
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
        foreach (self::getCards(Locations::REVEALED) as $card) {
          self::transferToHand($card);
        }
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
        'location_from'  => 'revealed,hand',
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