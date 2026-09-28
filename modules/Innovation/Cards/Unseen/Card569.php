<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card569 extends AbstractCard
{

  // Area 51:
  //   - You may splay your green cards up.
  //   - Choose to either draw an [11], or safeguard an available standard achievement.
  //   - Reveal one of your secrets, and super-execute it if it is your turn.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    if (self::isPostExecution()) {
      $secret = self::getCard(self::getAuxiliaryValue());
      if ($secret && self::getLocation($secret) === Locations::REVEALED) {
        self::putBackInSafe($secret);
      }
      return;
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayUp(Colors::GREEN);
    } else if (self::isSecondNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->choose([1, 2]);
      } else {
        return self::youMust()->safeguard();
      }
    } else {
      return self::youMust()->reveal()->fromYourSafe();
    }

  }

  public function afterInteraction()
  {
    if (self::isThirdNonDemand() && self::getNumChosen() > 0) {
      $secret = self::getLastSelectedCard();
      self::setAuxiliaryValue(self::getId($secret));
      if (self::isTheirTurn()) {
        // Put the secret back after nested dogma finishes; putting it back first
        // races the super-execute and can throw a server error.
        self::superExecute($secret);
      } else {
        self::putBackInSafe($secret);
      }
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