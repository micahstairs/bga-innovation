<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card203 extends AbstractCard
{
  // The Big Bang
  // - 3rd edition:
  //   - Execute the non-demand effects of your top blue card, without sharing. If this caused any
  //     change to occur, draw and remove a [10] from the game, then repeat this effect.
  // - 4th edition:
  //   - Self-execute your top blue card. If this causes any change to occur, draw and junk a [10],
  //     then repeat this effect.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::getPostExecutionIndex() > 0) {
      // NOTE: The logic which sets the auxiliary value to 1 is in the recordThatChangeOccurred() method.
      if (self::getAuxiliaryValue() === 1) {
        self::setPostExecutionIndex(0);
        if (self::isFirstOrThirdEdition()) {
          self::remove(self::draw(10));
        } else {
          self::junk(self::draw(10));
        }
      } else {
        return;
      }
    }

    self::setAuxiliaryValue(0); // Indicate that no change has occurred yet
    self::selfExecute(self::getTopCardOfColor(Colors::BLUE));
  }

}