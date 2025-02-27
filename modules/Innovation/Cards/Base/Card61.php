<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card61 extends AbstractCard
{
  // Canning:
  // - 3rd edition:
  //   - You may draw and tuck a [6]. If you do, score all your top cards without a [INDUSTRY].
  //   - You may splay your yellow cards right.
  // - 4th edition:
  //   - You may draw and tuck a [6]. If you tuck a card, score a top card without [INDUSTRY] of each color on your board.
  //   - You may splay your yellow cards right.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->choose([1])->build();
    } else {
      return self::youMay()->splayRight()->withColor(Colors::YELLOW)->build();
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw and tuck a ${age}'), 'age' => self::renderValue(6)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    self::drawAndTuck(6);
    foreach (self::getTopCards() as $card) {
      if (!self::hasIcon($card, Icons::INDUSTRY)) {
        self::score($card);
      }
    }
  }

}