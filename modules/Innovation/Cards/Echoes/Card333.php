<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card333 extends AbstractCard
{

  // Bangle
  // - 3rd edition:
  //   - ECHO: Tuck a red card from your hand.
  //   - Draw and foreshadow a [3].
  // - 4th edition:
  //   - ECHO: Tuck a [1] from your hand.
  //   - Choose to either draw and foreshadow a [2], or tuck a [2] from your forecast.
  //   - If you have no cards in your forecast, draw and foreshadow a [3].

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(3);
      } else {
        self::setMaxSteps(1);
      }
    } else if (self::countCards(Locations::FORECAST) === 0) {
      self::drawAndForeshadow(3);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstOrThirdEdition()) {
      return self::youMust()->tuck()->withColor(Colors::RED)->fromYourHand()->revealingIfUnable();
    } else {
      if (self::isEcho()) {
        return self::youMust()->tuck()->value(1)->fromYourHand();
      } else if (self::isFirstInteraction()) {
        return self::youMust()->choose([1, 2]);
      } else {
        return self::youMust()->tuck()->value(2)->fromYourForecast();
      }
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => self::renderValue(2)],
      2 => [clienttranslate('Tuck a ${age} from your forecast'), 'age' => self::renderValue(2)],
    ]);
  }

  public function handleListChoice($choice)
  {
    if ($choice === 1) {
      self::drawAndForeshadow(2);
    } else {
      self::setMaxSteps(2);
    }
  }

}