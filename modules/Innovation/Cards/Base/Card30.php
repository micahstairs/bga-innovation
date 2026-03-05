<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card30 extends AbstractCard
{
  // Paper:
  // - 3rd edition:
  //   - You may splay your green or blue cards left.
  //   - Draw a [4] for every color you have splayed left.
  // - 4th edition:
  //   - You may splay your green or blue cards left.
  //   - Score a top card with [HEALTH] from your board. If you do, draw a [4] for every color you have splayed left.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::isFourthEdition()) {
      self::setMaxSteps(1);
      ;
    } else {
      self::drawForEveryColorSplayedLeft();
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayLeft([Colors::GREEN, Colors::BLUE]);
    } else {
      return self::youMust()->score()->withIcon(Icons::HEALTH)->fromYourBoard();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::drawForEveryColorSplayedLeft();
  }

  private function drawForEveryColorSplayedLeft()
  {
    $numColorsSplayedLeft = self::countSplayedColors([Directions::LEFT]);
    for ($i = 0; $i < $numColorsSplayedLeft; $i++) {
      self::draw(4);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::canSplay([Colors::GREEN, Colors::BLUE])) {
      return true;
    }
    if (self::isFirstOrThirdEdition()) {
      return self::countSplayedColors([Directions::LEFT]) > 0;
    } else {
      return count(self::filterByIcon(self::getTopCards(), Icons::HEALTH)) > 0;
    }
  }

}