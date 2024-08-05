<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;


class Card92 extends AbstractCard
{
  // Suburbia:
  // - 3rd edition:
  //   - You may tuck any number of cards from your hand. Draw and score a 1 for each card you tucked.
  // - 4th edition:
  //   - You may tuck any number of cards from your hand. Draw and score a 1 for each card you tuck.
  //   - You may junk all cards in the 9 deck.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::getBaseDeckCount(9) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 'all',
        'location_from' => Locations::HAND,
        'tuck_keyword'  => true,
      ];
    } else {
      return [
        'can_pass' => true,
        'choices'  => [9],
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      9 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(9, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    self::junkBaseDeck(9);
  }

}