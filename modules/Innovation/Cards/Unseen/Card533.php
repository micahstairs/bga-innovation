<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card533 extends AbstractCard
{

  // Pantheism:
  //   - Tuck a card from your hand. If you do, draw and tuck a [4], score all cards on your board
  //     of the color of one of the tucked cards, and splay right the color on your board of the
  //     other tucked card.
  //   - Draw and tuck a [4].

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      self::drawAndTuck(4);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => 'hand',
        'tuck_keyword'  => true,
      ];
    } else {
      return [
        'choose_color' => true,
        'color'        => self::getAuxiliaryArray(),
      ];
    }
  }

  public function handleCardChoice(array $tuckedCard1)
  {
    $tuckedCard2 = self::drawAndTuck(4);
    if ($tuckedCard1['color'] == $tuckedCard2['color']) {
      while (($card = self::getTopCardOfColor($tuckedCard1['color'])) !== null) {
        self::score($card);
      }
    } else {
      self::setAuxiliaryArray([$tuckedCard1['color'], $tuckedCard2['color']]);
      self::setMaxSteps(2);
    }
  }

  public function handleColorChoice(int $color): void
  {
    foreach (array_reverse(self::getStack($color)) as $card) {
      self::score($card);
    }
    self::removeFromAuxiliaryArray($color);
    self::splayRight(self::getAuxiliaryArray()[0]);
  }

}