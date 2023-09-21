<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card149 extends AbstractCard
{

  // Molasses Reef Caravel
  // - 3rd edition:
  //   - Return all cards from your hand. Draw three [4]. Meld a blue card from your hand. Score a
  //     card from your hand. Return a card from your score pile.
  // - 4th edition:
  //   - Return all cards from your hand.
  //   - Draw three [4]. Meld a green card from your hand. Junk all cards in the deck of value
  //     equal to your top green card.


  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      self::setMaxSteps(4);
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::draw(4);
      self::draw(4);
      self::draw(4);
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::getInteractionOptionsForThirdEdition();
    } else if (self::isFirstNonDemand()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
        'color'         => [Colors::GREEN],
      ];
    }
  }

  private function getInteractionOptionsForThirdEdition(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'n'              => 'all',
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
        'color'         => [Colors::BLUE],
      ];
    } else if (self::isThirdInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    } else {
      return [
        'location_from'  => Locations::SCORE,
        'return_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFourthEdition() && self::isSecondInteraction()) {
      self::junkBaseDeck(self::getTopCardOfColor(Colors::GREEN)['faceup_age']);
    }
  }

}