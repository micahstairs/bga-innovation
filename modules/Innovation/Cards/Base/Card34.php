<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card34 extends AbstractCard
{
  // Feudalism:
  // - 3rd edition:
  //   - I DEMAND you transfer a card with a [AUTHORITY] from your hand to my hand! If you do,
  //     unsplay that color of your cards!
  //   - You may splay your yellow or purple cards left.
  // - 4th edition:
  //   - I DEMAND you transfer a card with [AUTHORITY] from your hand to my hand! If you do, junk
  //     all available special achievements!
  //   - You may splay your yellow or purple cards left. If you do, draw a [3].

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->fromYourHand()->withIcon(Icons::AUTHORITY)->toMine()->revealingIfUnable()->build();
    } else {
      return self::youMay()->splayLeft()->withColor([Colors::YELLOW, Colors::PURPLE])->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFourthEdition()) {
      self::junkCards(self::getAvailableSpecialAchievements());
    } else {
      self::unsplay(self::getColor($card));
    }
  }

  public function handleSplayChoice(int $color, bool $splayChanged)
  {
    if (self::isFourthEdition() && $splayChanged) {
      self::draw(3);
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplayLeft(Colors::YELLOW, Colors::PURPLE);
  }

}