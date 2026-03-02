<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card387 extends AbstractCard
{

  // Loom
  // - 3rd edition
  //   - ECHO: Score your lowest top card.
  //   - You may return two cards of different value from your score pile. If you do, draw and tuck
  //     three [6].
  //   - If you have five or more [IMAGE] visible on your board in one color, claim the Heritage
  //     achievement.
  // - 4th edition
  //   - ECHO: Score your lowest top card.
  //   - You may return exactly two cards of different value from your score pile. If you do, draw
  //     and tuck three [6].
  //   - If you have at least five [IMAGE] on your board in one color, claim the Heritage achievement.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (count(self::getUniqueValuesInLocation(Locations::SCORE)) >= 2) {
        self::setMaxSteps(1);
      }
    } else {
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::HEX_IMAGE) >= 5) {
          self::claim(CardIds::HERITAGE);
          break;
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $value = self::getMinValue(self::getTopCards());
      return self::youMust()->score()->value($value)->fromYourBoard()->build();
    } else if (self::isFirstInteraction()) {
      return self::youMay()->return()->fromYourScore()->build();
    } else {
      return self::youMust()->return()->onlyCardsInAuxiliaryArray()->fromYourScore()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        $cardIds = [];
        foreach (self::getCards(Locations::SCORE) as $scoreCard) {
          if (self::getValue($scoreCard) != self::getValue($card)) {
            $cardIds[] = self::getId($scoreCard);
          }
        }
        self::setAuxiliaryArray($cardIds);
        self::setMaxSteps(2);
      } else {
        self::drawAndTuck(6);
        self::drawAndTuck(6);
        self::drawAndTuck(6);
      }
    }
  }

}