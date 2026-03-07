<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card348 extends AbstractCard
{

  // Horseshoes
  // - 3rd edition:
  //   - ECHO: Draw and foreshadow a [2].
  //   - I DEMAND you transfer a top card without a [AUTHORITY] or [INDUSTRY] from your board to my
  //     board! If you do, draw and meld a [2]!
  // - 4th edition:
  //   - ECHO: You may draw and foreshadow a [2] or [3].
  //   - I DEMAND you transfer a top card without [AUTHORITY] or [INDUSTRY] from your board to my
  //     board! If you do, draw and meld a [2]!

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(2);
      } else if (self::getBaseDeckCount(2) > 0 || self::getBaseDeckCount(3) > 0) {
        self::setMaxSteps(1);
      } else {
        self::drawAndForeshadow(2); // Doesn't matter which is chosen since the player will be drawing up past [3] anyway
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'can_pass' => true,
        'choices'  => [2, 3],
      ];
    } else {
      return [
        'location_from' => Locations::BOARD,
        'owner_to'      => self::getLauncherId(),
        'location_to'   => Locations::BOARD,
        'without_icons' => [Icons::AUTHORITY, Icons::INDUSTRY],
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      2 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => self::renderValue(2)],
      3 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => self::renderValue(3)],
    ]);
  }

  public function handleListChoice(int $choice)
  {
    // TODO(LATER): Simplify this to `self::drawAndForeshadow($choice);` after this hits production.
    if ($choice === 3) {
      self::drawAndForeshadow(3);
    } else {
      self::drawAndForeshadow(2);
    }
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndMeld(2);
  }

}