<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;

class Card380 extends AbstractCard
{

  // Seed Drill
  // - 3rd edition:
  //   - I DEMAND you return a top card from your board of value less than [3]!
  //   - Choose the [3], [4], or [5] deck. If there is at least one card in that deck, you may
  //     transfer its bottom card to the available achievements.
  // - 4th edition:
  //   - I DEMAND you return a top card from your board of value less than 3!
  //   - Choose the [3], [4], or [5] deck. You may junk all cards in the chosen deck. If you do,
  //     achieve the highest card in the junk if eligible.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->return()->minValue(1)->maxValue(2)->fromYourBoard();
    } else if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue([3, 4, 5]);
    } else if (self::isSecondInteraction()) {
      return self::youMay()->choose([1]);
    } else {
      $value = self::getMaxValueInLocation('junk');
      return self::youMust()->achieveIfEligible()->value($value)->fromJunk();
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

  public function handleValueChoice($value)
  {
    if (self::getBaseDeckCount($value) > 0) {
      self::setAuxiliaryValue2($value); // Track which deck was chosen
      self::setMaxSteps(2);
    }

  }

  public function handleListChoice(int $choice)
  {
    if (self::isFirstOrThirdEdition()) {
      // TODO(LATER): This shouldn't really be a draw.
      $this->game->executeDraw(0, /*age=*/ self::getAuxiliaryValue2(), 'achievements', /*bottom_to=*/ false, 0, /*bottom_from=*/ true);
    } else {
      if (self::junkBaseDeck(self::getAuxiliaryValue2())) {
        self::setMaxSteps(3);
      }
    }
  }

}