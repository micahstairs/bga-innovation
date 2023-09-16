<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card524 extends Card
{

  // Legend:
  //   - Choose a non-purple color. Self-execute your top card of that color. Score your top card
  //     of that color. If you do, repeat this effect with the same color if you have scored fewer
  //     than nine points due to Legend during this action.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::getPostExecutionIndex() > 0) {
      self::setPostExecutionIndex(0);
      self::scoreTopCardAndPotentiallyRepeat();
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_color' => true,
      'color'        => Colors::NON_PURPLE,
    ];
  }

  public function handleColorChoice(int $color): void
  {
    self::setAuxiliaryValue($color);
    self::selfExecuteTopCard($color);
  }

  private function scoreTopCardAndPotentiallyRepeat(): void {
    $color = self::getAuxiliaryValue();
    $topCard = self::getTopCardOfColor($color);
    if ($topCard) {
      self::score($topCard);
      $scores = self::getActionScopedAuxiliaryArray(self::getPlayerId());
      $scores[] = $topCard['faceup_age'];
      self::setActionScopedAuxiliaryArray($scores, self::getPlayerId());
      if (array_sum($scores) < 9) {
        self::selfExecuteTopCard($color);
      }
    }
  }

  private function selfExecuteTopCard($color): void {
    $topCard = self::getTopCardOfColor($color);
    if (!self::selfExecute($topCard)) {
      self::scoreTopCardAndPotentiallyRepeat();
    }
  }

}