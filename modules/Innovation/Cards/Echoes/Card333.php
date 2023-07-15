<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card333 extends Card
{

  // Bangle
  // - 3rd edition:
  //   - ECHO: Tuck a red card from your hand.
  //   - Draw and foreshadow a [3].
  // - 4th edition:
  //   - ECHO: Tuck a [1] from your hand.
  //   - Choose to either draw and foreshadow a [2], or tuck a [2] from your forecast.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstOrThirdEdition()) {
      self::drawAndForeshadow(3);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return [
        'location_from' => 'hand',
        'location_to'   => 'board',
        'bottom_to'     => true,
        'color'         => [$this->game::RED],
      ];
    } else {
      if (self::isEcho()) {
        return [
          'location_from' => 'hand',
          'location_to'   => 'board',
          'bottom_to'     => true,
          'age'           => 1,
        ];
      } else if (self::getCurrentStep() === 1) {
        return ['choices' => [1, 2]];
      } else {
        return [
          'location_from' => 'forecast',
          'location_to'   => 'board',
          'bottom_to'     => true,
          'age'           => 2,
        ];
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      1 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => $this->game->getAgeSquare(2)],
      2 => [clienttranslate('Tuck a ${age} from your forecast'), 'age' => $this->game->getAgeSquare(2)],
    ]);
  }

  public function handleSpecialChoice($choice)
  {
    if ($choice === 1) {
      self::drawAndForeshadow(2);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction(): void
  {
    if (self::isFirstOrThirdEdition() && self::isEcho() && self::getNumChosen() === 0) {
      $this->game->revealHand(self::getPlayerId());
    }
  }

}