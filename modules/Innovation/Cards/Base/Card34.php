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
  //   - I DEMAND you transfer a card with a [AUTHORITY] from your hand to my hand! If you do, unsplay that color of your cards!
  //   - You may splay your yellow or purple cards left.
  // - 4th edition:
  //   - I DEMAND you transfer a card with [AUTHORITY] from your hand to my hand! If you do, junk all available special achievements!
  //   - You may splay your yellow or purple cards left. If you do, draw a [3].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location'         => Locations::HAND,
        'owner_from'       => self::getPlayerId(),
        'owner_to'         => self::getLauncherId(),
        'with_icon'        => Icons::AUTHORITY,
        'reveal_if_unable' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [Colors::YELLOW, Colors::PURPLE],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFourthEdition()) {
      $availableSpecialAchievements = [];
      foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $achievement) {
        if (self::isSpecialAchievement($achievement)) {
          $availableSpecialAchievements[] = $achievement;
        }
      }
      self::junkCards($availableSpecialAchievements);
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

}