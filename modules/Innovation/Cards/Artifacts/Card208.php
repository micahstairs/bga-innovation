<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card208 extends AbstractCard
{
  // Maldives
  //   - I COMPEL you to return all cards in your hand but two! Return all cards in your score pile but two!
  //   - Return all cards in your score pile but four.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      if (self::isFirstInteraction()) {
        return [
          'n'              => self::countCards(Locations::HAND) - 2,
          'location_from'  => Locations::HAND,
          'return_keyword' => true,
        ];
      } else {
        return [
          'n'              => self::countCards(Locations::SCORE) - 2,
          'location_from'  => Locations::SCORE,
          'return_keyword' => true,
        ];
      }
    } else {
      return [
        'n'              => self::countCards(Locations::SCORE) - 4,
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    }
  }

}