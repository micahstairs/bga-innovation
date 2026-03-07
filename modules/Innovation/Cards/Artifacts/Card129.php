<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;

class Card129 extends AbstractCard
{

  // Holy Lance
  //   - I COMPEL you to transfer a top Artifact from your board to my board!
  //   - If Holy Grail is a top card on your board, you win.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::holyGrailIsTopCard()) {
        self::win();
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->ofType(CardTypes::ARTIFACTS)->fromYourBoard()->toMine();
  }

  private function holyGrailIsTopCard(): bool
  {
    $topYellowCard = self::getTopCardOfColor(Colors::YELLOW);
    return $topYellowCard && self::getId($topYellowCard) == CardIds::HOLY_GRAIL;
  }

  public function compelMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::getType($card) == CardTypes::ARTIFACTS) {
        return true;
      }
    }
    return false;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return $this->holyGrailIsTopCard();
  }

}