<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card426 extends AbstractCard
{

  // Human Genome
  // - 3rd edition 
  //   - You may draw and score a card of any value. Take a bottom card from your board into your
  //     hand. If the values of all of the cards in your hand match the values of all the cards in
  //     your score pile exactly, you win.
  // - 4th edition
  //   - ECHO: Draw an [11].
  //   - You may draw and score a card of any value. Transfer your bottom red card to your hand. If
  //     the values of all the cards in your hand match the values of all the cards in your score
  //     pile exactly, you win.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(11);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->chooseValue();
    } else {
      $colors = self::isFourthEdition() ? Colors::RED : Colors::ALL;
      return self::youMust()->withColor($colors)->fromBottom()->fromYourBoard()->toMyHand();
    }
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndScore($value);
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $handCounts = self::countCardsKeyedByValue(Locations::HAND);
      $scoreCounts = self::countCardsKeyedByValue(Locations::SCORE);
      $eligible = true;
      for ($i = 1; $i <= 11; $i++) {
        if ($handCounts[$i] != $scoreCounts[$i]) {
          $eligible = false;
          break;
        }
      }
      if ($eligible) {
        self::win();
      }
    }
  }

}