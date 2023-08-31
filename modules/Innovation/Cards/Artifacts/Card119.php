<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card119 extends Card
{

  // Dancing Girl
  // - 3rd edition:
  //   - I COMPEL you to transfer Dancing Girl to your board!
  //   - If Dancing Girl has been on every board during this action, and it started on your board, you win.
  // - 4th edition:
  //   - I COMPEL you to transfer Dancing Girl to your board!
  //   - If Dancing Girl has been on every opponent's board during this action, and it is your turn, you win.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::transferToBoard(self::getCard(self::getThisCardId()));
      self::addToActionScopedAuxiliaryArray(self::getPlayerId());
    } else if (self::isFirstOrThirdEdition() && self::wasEveryBoardAndStartedOnTheirBoard()) {
      self::win();
    } else if (self::isFourthEdition() && self::wasOnEveryOpponentBoardAndIsTheirTurn()) {
      self::win();
    }
  }

  private function wasEveryBoardAndStartedOnTheirBoard(): bool
  {
    if (array_diff(self::getOtherPlayerIds(), self::getActionScopedAuxiliaryArray())) {
      return false;
    }
    return self::isLauncher() && $this->game->getCurrentNestedCardState()['card_location'] === 'board';
  }

  private function wasOnEveryOpponentBoardAndIsTheirTurn(): bool
  {
    if (array_diff(self::getOpponentIds(), self::getActionScopedAuxiliaryArray())) {
      return false;
    }
    return self::isTheirTurn();
  }

}