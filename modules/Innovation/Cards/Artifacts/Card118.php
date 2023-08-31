<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card118 extends Card
{

  // Jiskairumoko Necklace
  // - 3rd edition:
  //   - I COMPEL you to return a card from your score pile! If you do, transfer an achievement of
  //     the same value from your achievements to mine!
  // - 4th edition:
  //   - I COMPEL you to return a card from your score pile! If you do, transfer an achievement of
  //     the same value from your achievements to mine, and junk all cards in the deck of that value!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    } else {
      return [
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'achievements',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'achievements',
        'age'           => self::getLastSelectedAge(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue($card['age']);
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction() {
    if (self::isFourthEdition() && self::isSecondInteraction()) {
      self::junkBaseDeck(self::getAuxiliaryValue());
    }
  }

}