<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card161 extends AbstractCard
{
  // Gujin Tushu Jinsheng (3rd edition):
  //   - If Gujin Tushu Jinsheng is on your board, choose any other top card on any other board.
  //     Execute the effects on the chosen card as if they were on this card. Do not share them.
  // Gujin Tushu Jicheng (4th edition):
  //   - If it is your turn, choose any other top card on any other board and super-execute it.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      $topYellowCard = self::getTopCardOfColor(Colors::YELLOW);
      if ($topYellowCard && self::getId($topYellowCard) == CardIds::GUJIN_TUSHU_JICHENG) {
        self::setMaxSteps(1);
      }
    } else {
      if (self::isTheirTurn()) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyOtherPlayer()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::superExecute($card);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    // Technically, there are situations where this is not effective, but the check is complex and not worth it.
    return true;
  }

}