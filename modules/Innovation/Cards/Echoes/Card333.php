<?php

namespace Innovation\Cards\Echoes;

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
        'tuck_keyword'  => true,
        'color'         => [$this->game::RED],
      ];
    } else {
      if (self::isEcho()) {
        return [
          'location_from' => 'hand',
          'tuck_keyword'  => true,
          'age'           => 1,
        ];
      } else if (self::isFirstInteraction()) {
        return ['choices' => [1, 2]];
      } else {
        return [
          'location_from' => 'forecast',
          'tuck_keyword'  => true,
          'age'           => 2,
        ];
      }
    }
  }

  public function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => self::renderValue(2)],
      2 => [clienttranslate('Tuck a ${age} from your forecast'), 'age' => self::renderValue(2)],
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

  public function afterInteraction()
  {
    if (self::isFirstOrThirdEdition() && self::isEcho() && self::getNumChosen() === 0) {
      self::revealHand();
    }
  }

}