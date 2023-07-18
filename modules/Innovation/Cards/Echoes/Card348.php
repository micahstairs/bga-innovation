<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card348 extends Card
{

  // Horseshoes
  // - 3rd edition:
  //   - ECHO: Draw and foreshadow a [2].
  //   - I DEMAND you transfer a top card without a [AUTHORITY] or [INDUSTRY] from your board to my
  //     board! If you do, draw and meld a [2]!
  // - 4th edition:
  //   - ECHO: You may draw and foreshadow a [2] or [3].
  //   - I DEMAND you transfer a top card without a [AUTHORITY] or [INDUSTRY] from your board to my
  //     board! If you do, draw and meld a [2]!

  public function initialExecution()
  {
    if (self::isEcho() && self::isFirstOrThirdEdition()) {
      self::drawAndForeshadow(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'can_pass' => true,
        'choices' => [1, 2],
      ];
    } else {
      return [
          'location_from' => 'board',
          'owner_to' => self::getLauncherId(),
          'location_to' => 'board',
          'without_icons' => [$this->game::AUTHORITY, $this->game::INDUSTRY],
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      1 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => $this->game->getAgeSquare(2)],
      2 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => $this->game->getAgeSquare(3)],
    ]);
  }

  public function handleSpecialChoice(int $choice) {
    if ($choice === 1) {
      self::drawAndForeshadow(2);
    } else {
      self::drawAndForeshadow(3);
    }
  }

  public function handleCardChoice(array $card) {
    self::drawAndMeld(2);
  }

}