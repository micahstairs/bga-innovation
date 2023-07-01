<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card524 extends Card
{

  // Legend:
  //   - Choose a non-purple color. Self-execute your top card of that color. Score your top card
  //     of that color. If you do, repeat this effect with the same color if you have scored fewer
  //     than nine points due to Legend this action.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::getPostExecutionIndex() > 0) {
      $topCard = self::getTopCardOfColor(self::getAuxiliaryValue());
      if ($topCard) {
        self::score($topCard);
        $scores = self::getActionScopedAuxiliaryArray(self::getPlayerId());
        $scores[] = $topCard['age'];
        if (array_sum($scores) >= 9) {
          return;
        }
        self::setActionScopedAuxiliaryArray($scores, self::getPlayerId());
      }
      self::setPostExecutionIndex(0);
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_color' => true,
      'color'        => self::getAllColorsOtherThan($this->game::PURPLE),
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::selfExecute(self::getTopCardOfColor($choice));
    self::setAuxiliaryValue($choice);
  }

}