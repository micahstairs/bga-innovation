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
      if ($topYellowCard && $topYellowCard['id'] == CardIds::GUJIN_TUSHU_JICHENG) {
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
    return [
      'owner_from' => 'any other player',
      'choose_from' => Locations::BOARD,
    ];
  }

  public function handleCardChoice(array $card) {
    self::fullyExecute($card);
  }

}