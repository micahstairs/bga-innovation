<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card431 extends AbstractCard
{

  // Cell Phone
  // - 3rd edition 
  //   - Draw a [10] for every two [EFFICIENCY] on your board.
  //   - You may splay your green cards up.
  //   - You may tuck any number of cards with a [EFFICIENCY] from your hand, splaying up each
  //     color you tucked into.
  // - 4th edition
  //   - ECHO: Draw and foreshadow an [11].
  //   - Draw a [10] for every color on your board with [EFFICIENCY].
  //   - You may splay your green cards up.
  //   - You may tuck any number of cards with [EFFICIENCY] from your hand, splaying up each
  //     color into which you tuck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndForeshadow(11);
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstOrThirdEdition()) {
        $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::EFFICIENCY), 2);
      } else {
        $numCards = self::countColorsWithIcon(Icons::EFFICIENCY);
      }
      for ($i = 0; $i < $numCards; $i++) {
        self::draw(10);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isSecondNonDemand()) {
      return self::youMay()->splayUp(Colors::GREEN)->build();
    } else {
      if (self::isFourthEdition()) {
        // In 4th edition, need to refresh in case a splay causes a City to be drawn
        return self::youMay()->tuck()->anyNumber()->withIcon(Icons::EFFICIENCY)->fromYourHand()->refreshingSelection()->build();
      } else {
        return self::youMay()->tuck()->anyNumber()->withIcon(Icons::EFFICIENCY)->fromYourHand()->build();
      }
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isThirdNonDemand()) {
      self::splayUp(self::getColor($card));
    }
  }

}