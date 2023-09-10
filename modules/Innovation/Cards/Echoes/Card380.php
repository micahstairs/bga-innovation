<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\CardTypes;

class Card380 extends Card
{

  // Seed Drill
  // - 3rd edition:
  //   - I DEMAND you return a top card from your board of value less than [3]!
  //   - Choose the [3], [4], or [5] deck. If there is at least one card in that deck, you may
  //     transfer its bottom card to the available achievements.
  // - 4th edition:
  //   - I DEMAND you return a top card from your board of value less than [3]!
  //   - Choose the [3], [4], or [5] deck. You may junk all cards in the chosen deck. If you do,
  //     achieve the highest junked card if eligible.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location_from'  => 'board',
        'return_keyword' => true,
        'age_min'        => 1,
        'age_max'        => 2,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'choose_value' => true,
        'age'          => [3, 4, 5],
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    } else {
      return [
        'location_from'       => 'junk',
        'achieve_if_eligible' => true,
        'age'                 => self::getMaxValueInLocation('junk'),
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::buildPromptFromList([
        1 => [
          clienttranslate('Transfer bottom card from ${age} deck to the available achievements'),
          'age' => self::renderValueWithType(self::getAuxiliaryValue2(), CardTypes::BASE),
        ],
      ]);
    } else {
      return self::buildPromptFromList([
        1 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(self::getAuxiliaryValue2(), CardTypes::BASE)],
      ]);
    }
  }

  public function handleSpecialChoice($choice)
  {
    if (self::isFirstInteraction()) {
      if ($this->game->countCardsInLocationKeyedByAge(0, 'deck', CardTypes::BASE) > 0) {
        self::setAuxiliaryValue2($choice); // Track which deck was chosen
        self::setMaxSteps(2);
      }
    } else if (self::isFirstOrThirdEdition()) {
      // TODO(LATER): This shouldn't really be a draw.
      $this->game->executeDraw(0, /*age=*/self::getAuxiliaryValue2(), 'achievements', /*bottom_to=*/false, 0, /*bottom_from=*/true);
    } else {
      if (self::junkBaseDeck(self::getAuxiliaryValue2())) {
        self::setMaxSteps(3);
      }
    }
  }

}