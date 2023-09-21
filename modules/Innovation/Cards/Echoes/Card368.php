<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card368 extends AbstractCard
{

  // Shuriken
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-red card with a [AUTHORITY] or [CONCEPT] from your board
  //     to my board! If you do, draw a [4]!
  //   - You may splay your purple cards right.
  // - 4th edition:
  //   - I DEMAND you transfer two non-red top cards with a [AUTHORITY] or [AVATAR] from your board
  //     to my board! If you do, and Shuriken was foreseen, you lose!
  //   - You may splay your purple cards right.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition()) {
        return [
          'location_from' => 'board',
          'owner_to'      => self::getLauncherId(),
          'location_to'   => 'board',
          'color'         => Colors::NON_RED,
          'with_icons'    => [Icons::AUTHORITY, Icons::CONCEPT],
        ];
      } else {
        return [
          'n'             => 2,
          'location_from' => 'board',
          'owner_to'      => self::getLauncherId(),
          'location_to'   => 'board',
          'color'         => Colors::NON_RED,
          'with_icons'    => [Icons::AUTHORITY, Icons::AVATAR],
        ];
      }
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::PURPLE],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition() && self::getNumChosen() === 1) {
        self::draw(4);
      } else if (self::wasForeseen() && self::getNumChosen() === 2) {
        self::lose();
      }
    }
  }

}