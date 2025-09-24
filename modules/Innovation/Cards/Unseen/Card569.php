<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card569 extends AbstractCard
{

  // Area 51:
  //   - You may splay your green cards up.
  //   - Choose to either draw an [11], or safeguard an available standard achievement.
  //   - Reveal one of your secrets, and super-execute it if it is your turn.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayUp(Colors::GREEN)->build();
    } else if (self::isSecondNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->choose([1, 2])->build();
      } else {
        return self::youMust()->safeguard()->build();
      }
    } else {
      return self::youMust()->reveal()->fromYourSafe()->build();
    }

  }

  public function afterInteraction()
  {
    if (self::isThirdNonDemand() && self::getNumChosen() > 0) {
      if (self::isTheirTurn()) {
        self::superExecute(self::getLastSelectedCard());
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

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      self::draw(11);
    } else {
      self::setMaxSteps(2);
    }
  }

}