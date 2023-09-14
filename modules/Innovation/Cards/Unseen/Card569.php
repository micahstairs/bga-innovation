<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card569 extends Card
{

  // Area 51:
  //   - You may splay your green cards up.
  //   - Choose to either draw an [11], or safeguard an available standard achievement.
  //   - Reveal one of your secrets, and fully execute it if it is your turn.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::GREEN],
      ];
    } else if (self::isSecondNonDemand()) {
      if (self::isFirstInteraction()) {
        return ['choices' => [1, 2]];
      } else {
        return ['safeguard_keyword' => true];
      }
    } else {
      return [
        'location_from' => 'safe',
        'location_to'   => 'revealed',
      ];
    }

  }

  public function afterInteraction()
  {
    if (self::isThirdNonDemand() && self::getNumChosen() > 0) {
      if (self::isTheirTurn()) {
        self::fullyExecute(self::getLastSelectedCard());
      }
      self::putBackInSafe(self::getLastSelectedCard());
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw an ${age}'), 'age' => self::renderValue(11)],
      2 => clienttranslate('Safeguard an available achievement'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      self::draw(11);
    } else {
      self::setMaxSteps(2);
    }
  }

}