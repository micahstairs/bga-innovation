<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card403 extends Card
{

  // Ice Cream
  // - 3rd edition
  //   - ECHO: Score a non-purple top card from your board without a bonus.
  //   - I DEMAND you draw and meld a [1]!
  //   - Choose the [6], [7], [8], [9] or deck. If there is at least one card in that deck, you may
  //     transfer its bottom card to the available achievements.
  // - 4th edition
  //   - ECHO: Score a non-purple top card from your board without a bonus.
  //   - I DEMAND you draw and meld a [1]!
  //   - Choose the [6], [7], [8], or [9] deck. You may junk all cards in the chosen deck. If you
  //     do, achieve the highest junked card if eligible.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::drawAndMeld(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'board',
        'score_keyword' => true,
        'without_bonus' => true,
        'color'         => self::getAllColorsOtherThan($this->game::PURPLE),
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'choose_value' => true,
        'age'          => [6, 7, 8, 9],
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    } else {
      return [
        'location_from'                   => 'junk',
        'location_to'                     => 'achievements',
        'require_achievement_eligibility' => true,
        'age'                             => self::getMaxValueInLocation('junk'),
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    if (self::isFirstInteraction()) {
      return self::getPromptForValueChoice();
    } else {
      if (self::isFirstOrThirdEdition()) {
        $text = clienttranslate('Transfer bottom card from ${age} deck to the available achievements');
      } else {
        $text = clienttranslate('Junk ${age} deck');
      }
      return self::getPromptForChoiceFromList([
        1 => [$text, 'age' => $this->game->getAgeSquare(self::getAuxiliaryValue2())],
      ]);
    }
  }

  public function handleSpecialChoice($choice)
  {
    if (self::isFirstInteraction()) {
      $value = self::getAuxiliaryValue2();
      if ($this->game->countCardsInLocationKeyedByAge(0, 'deck', $this->game::BASE)[$value] > 0) {
        self::setAuxiliaryValue2($choice); // Track chosen deck
        self::setMaxSteps(2);
      }
    } else if (self::isFirstOrThirdEdition()) {
      // TODO(LATER): This shouldn't really be a draw.
      $this->game->executeDraw(0, /*age=*/self::getAuxiliaryValue2(), 'achievements', /*bottom_to=*/false, 0, /*bottom_from=*/true);
    } else if (self::junkBaseDeck(self::getAuxiliaryValue2())) {
      self::setMaxSteps(3);
    }
  }

}