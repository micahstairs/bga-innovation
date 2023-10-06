<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card485 extends AbstractCard
{

  // Pilgrimage:
  //   - You may return a [1] from your hand. If you do, safeguard an available achievement of
  //     value equal to the returned card, then repeat this effect using a value one higher.
  //   - You may junk all cards in the [1] deck.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(1); // Track the value to return next
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'can_pass'       => true,
          'location_from'  => Locations::HAND,
          'return_keyword' => true,
          'age'            => self::getAuxiliaryValue(),
        ];
      } else {
        return [
          'safeguard_keyword' => true,
          'age'               => self::getAuxiliaryValue(),
        ];
      }
    } else {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(1, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $choice)
  {
    self::junkBaseDeck(1);
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() > 0 && self::getLastSelectedAge() === self::getAuxiliaryValue()) {
        self::setMaxSteps(2);
      }
    } else {
      self::incrementAuxiliaryValue();
      self::setNextStep(1);
      self::setMaxSteps(1);
    }
  }
}