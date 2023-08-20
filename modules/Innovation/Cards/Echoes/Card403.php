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
    }
    if (self::isFirstOrThirdEdition()) {
      if (self::isFirstInteraction()) {
        return [
          'choose_value' => true,
          'age'          => [6, 7, 8, 9],
        ];
      } else {
        return [
          'can_pass' => true,
          'choices'  => [1],
        ];
      }
    }
    if (self::isFirstInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [6, 7, 8, 9],
      ];
    } else {
      return [
        'location_from'       => 'junk',
        'achieve_if_eligible' => true,
        'age'                 => self::getMaxValueInLocation('junk'),
      ];
    }
  }

  public function getPromptForListChoice(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::buildPromptFromList([
        1 => [
          clienttranslate('Transfer bottom card from ${age} deck to the available achievements'),
          'age' => self::renderValue(self::getAuxiliaryValue2()),
        ],
      ]);
    } else {
      return self::buildPromptFromList([
        6 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(6, $this->game::BASE)],
        7 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(7, $this->game::BASE)],
        8 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(8, $this->game::BASE)],
        9 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(9, $this->game::BASE)],
      ]);
    }
  }

  public function handleSpecialChoice(int $choice)
  {
    if (self::isFirstOrThirdEdition()) {
      if (self::isFirstInteraction()) {
        if ($this->game->countCardsInLocationKeyedByAge(0, 'deck', $this->game::BASE)[$choice] > 0) {
          self::setAuxiliaryValue2($choice); // Track chosen deck
          self::setMaxSteps(2);
        }
      } else {
        // TODO(LATER): This shouldn't really be a draw.
        $this->game->executeDraw(0, /*age=*/self::getAuxiliaryValue2(), 'achievements', /*bottom_to=*/false, 0, /*bottom_from=*/true);
      }
    } else if (self::junkBaseDeck($choice)) {
      self::setMaxSteps(2);
    }
  }

}