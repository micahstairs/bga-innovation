<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card339 extends AbstractCard
{

  // Chopsticks
  // - 3rd edition:
  //   - ECHO: Draw a [1].
  //   - If the [1] deck has at least one card, you may transfer its bottom card to the available achievements.
  // - 4th edition:
  //   - ECHO: You may draw and foreshadow a [1].
  //   - You may junk all cards in the [1] deck. If you do, achieve the highest card in the junk if eligible.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::draw(1);
      } else {
        self::setMaxSteps(1);
      }
    } else if (self::isFirstNonDemand() && self::getBaseDeckCount(1) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    } else {
      return [
        'location_from'       => Locations::JUNK,
        'achieve_if_eligible' => true,
        'age'                 => self::getMaxValueInLocation(Locations::JUNK),
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    if (self::isEcho()) {
      return self::buildPromptFromList([
        1 => [clienttranslate('Draw and foreshadow a ${age}'), 'age' => self::renderValue(1)],
      ]);
    } else if (self::isFirstOrThirdEdition()) {
      return self::buildPromptFromList([
        1 => [clienttranslate('Transfer the bottom ${age} to the available achievements'), 'age' => self::renderValue(1)],
      ]);
    } else {
      return self::buildPromptFromList([
        1 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(1, CardTypes::BASE)],
      ]);
    }
  }

  public function handleListChoice(int $choice): void
  {
    if (self::isEcho()) {
      self::drawAndForeshadow(1);
    } else if (self::isFirstOrThirdEdition()) {
      $this->game->executeDraw(0, /*age=*/1, Locations::ACHIEVEMENTS, /*bottom_to=*/false, /*type=*/0, /*bottom_from=*/true);
    } else {
      self::junkBaseDeck(1);
      self::setMaxSteps(2);
    }
  }

}