<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card588 extends AbstractCard
{

  // Dark Web:
  //   - Unsplay any color on any board.
  //   - Choose to either safeguard any number of available standard achievements, or achieve any
  //     number of secrets from your safe regardless of eligibility.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->build();
    } else {
      if (self::isFirstInteraction()) {
        return self::youMust()->choose([1, 2])->build();
      } else if (self::getAuxiliaryValue() === 1) {
        return self::youMay()->safeguard()->anyNumber()->build();
      } else {
        return self::youMay()->anyNumber()->fromYourSafe()->toYourAchivements()->build();
      }
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::getEffectNumber() === 1) {
      self::unsplay(self::getColor($card), $card['owner'], self::getPlayerId());
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Safeguard standard achievements'),
      2 => clienttranslate('Achieve secrets'),
    ]);
  }

  public function handleListChoice(int $choice)
  {
    self::setAuxiliaryValue($choice);
  }

}