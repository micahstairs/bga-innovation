<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card124 extends AbstractCard
{

  // Tale of the Shipwrecked Sailor
  // - 3rd edition:
  //   - Choose a color. Draw a [1]. Meld a card of the chosen color from your hand. If you do,
  //     splay that color left.
  // - 4th edition:
  //   - Choose a color. Draw a [1]. Meld a card of the chosen color from your hand. If you do,
  //     splay that color left, and junk an available achievement of value equal to the value of the melded card.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseColor()->build();
    } else if (self::isSecondInteraction()) {
      self::draw(1);
      return self::youMust()->meld()->withColor(self::getAuxiliaryValue())->fromYourHand()->revealingIfUnable()->build();
    } else {
      return self::youMust()->junk()->fromAvailableAchievements()->value(self::getLastSelectedFaceUpAge())->build();
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color); // Track color to meld
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondInteraction()) {
      self::splayLeft(self::getColor($card));
      if (self::isFourthEdition()) {
        self::setMaxSteps(3);
      }
    }
  }

}