<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card346 extends Card
{

  // Linguistics
  // - 3rd edition:
  //   - ECHO: Draw a [3] OR Draw and foreshadow a [4].
  //   - Draw a card of value equal to a bonus on your board, if you have any.
  // - 4th edition:
  //   - ECHO: Draw a [3] OR Draw and foreshadow a [4].
  //   - Draw a card of value equal to a bonus on any board, if there is one. If you do, and
  //     Linguistics was foreseen, junk all available achievements of that value.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return ['choices' => [1, 2]];
    } else if (self::isFirstInteraction()) {
      if (self::isFirstOrThirdEdition()) {
        $values = self::getBonuses();
      } else {
        $values = [];
        foreach (self::getPlayerIds() as $playerId) {
          $values = array_merge($values, self::getBonuses($playerId));
        }
      }
      return [
        'choose_value' => true,
        'age'          => $values,
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw a ${age}'), 'age' => self::renderValue(3)],
      2 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => self::renderValue(4)],
    ]);
  }

  public function handleSpecialChoice(int $choice)
  {
    if (self::isEcho()) {
      if ($choice === 1) {
        self::draw(3);
      } else {
        self::drawAndForeshadow(4);
      }
    } else {
      self::draw($choice);
      if (self::wasForeseen()) {
        self::setAuxiliaryValue($choice); // Track the value of the achievements which needs to be junked
        self::setMaxSteps(2);
      }
    }
  }

}