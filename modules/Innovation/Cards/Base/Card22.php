<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card22 extends AbstractCard
{

  // Fermenting:
  // - 3rd edition:
  //   - Draw a [2] for every color on your board with one or more [HEALTH].
  // - 4th edition:
  //   - Draw a [2] for every color on your board with [HEALTH].
  //   - You may tuck a green card from your hand. If you don't, junk all cards in the [2] deck,
  //     and junk Fermenting if it is a top card on any board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $numToDraw = self::countColorsWithIcon(Icons::HEALTH);
      for ($i = 0; $i < $numToDraw; $i++) {
        self::draw(2);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'      => true,
      'location_from' => Locations::HAND,
      'tuck_keyword'  => true,
      'color'         => [Colors::GREEN],
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      self::junkBaseDeck(2);
      self::junk($this->game->getIfTopCardOnBoard(CardIds::FERMENTING));
    }
  }

}