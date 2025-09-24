<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card537 extends AbstractCard
{

  // Red Herring:
  //   - Splay your red cards left, right, or up.
  //   - Draw and tuck a [6]. If the color on your board of the card you tuck is splayed in the
  //     same direction as your red  cards, splay that color up. Otherwise, unsplay that color.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      $card = self::drawAndTuck(6);
      if (self::getSplayDirection(self::getColor($card)) == self::getSplayDirection(Colors::RED)) {
        self::splayUp(self::getColor($card));
      } else {
        self::unsplay(self::getColor($card));
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->choose([1, 2, 3])->build();
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Splay red left'),
      2 => clienttranslate('Splay red right'),
      3 => clienttranslate('Splay red up'),
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      self::splayLeft(Colors::RED);
    } else if ($choice === 2) {
      self::splayRight(Colors::RED);
    } else {
      self::splayUp(Colors::RED);
    }
  }
}