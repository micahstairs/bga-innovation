<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card585 extends AbstractCard
{

  // Astrobiology:
  //   - Return a bottom card from your board. Splay that color on your board aslant. Score all
  //     cards on your board of that color without [HEALTH].

  public function getInteractionOptions(): array
  {
    return self::youMust()->return()->fromBottom()->fromYourBoard()->build();
  }

  public function handleCardChoice(array $card)
  {
    $color = self::getColor($card);
    self::splayAslant($color);
    foreach (self::getStack($color) as $card) {
      if (!self::hasIcon($card, Icons::HEALTH)) {
        self::score($card);
      }
    }
  }

}