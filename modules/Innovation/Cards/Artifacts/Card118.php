<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card118 extends AbstractCard
{

  // Jiskairumoko Necklace
  // - 3rd edition:
  //   - I COMPEL you to return a card from your score pile! If you do, transfer an achievement of
  //     the same value from your achievements to mine!
  // - 4th edition:
  //   - I COMPEL you to return a card from your score pile! If you do, transfer an achievement of
  //     the same value from your achievements to mine, and junk all cards in the deck of that value!

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    } else {
      return [
        'location'   => Locations::ACHIEVEMENTS,
        'owner_from' => self::getPlayerId(),
        'owner_to'   => self::getLauncherId(),
        'age'        => self::getLastSelectedAge(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(self::getValue($card));
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction()
  {
    if (self::isFourthEdition() && self::isSecondInteraction()) {
      self::junkBaseDeck(self::getAuxiliaryValue());
    }
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::SCORE) > 0;
  }

}