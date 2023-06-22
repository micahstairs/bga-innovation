<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card522 extends Card
{

  // Heirloom:
  //   - Transfer one of your secrets to the available achievements and 
  //     draw a card of value one higher than the transferred card. If you don't, 
  //     safeguard an available achievement of value equal to the value of your top red card.

  public function initialExecution()
  {
    $secrets = $this->game->getCardsInLocation(self::getPlayerId(), 'safe');
    if (count($secrets) == 1) {
      $this->game->transferCardFromTo($secrets[0], 0, 'achievements');
      self::draw($secrets[0]['age'] + 1);
    } else if (count($secrets) > 1) {
      self::setMaxSteps(1);
    } else {
      $topRedCard = self::getTopCardOfColor($this->game::RED);
      self::drawAndSafeguard($topRedCard === null ? 0 : $topRedCard['age']);
    }

  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'safe',
      'owner_to'      => 0,
      'location_to'   => 'achievements',
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::draw(self::getLastSelectedAge() + 1);
    } else {
      $topRedCard = self::getTopCardOfColor($this->game::RED);
      self::drawAndSafeguard($topRedCard === null ? 0 : $topRedCard['age']);
    }
  }

}