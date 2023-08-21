<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card364 extends Card
{

  // Sunglasses
  //   - ECHO: Score a card from your hand of a color you have splayed.
  //   - You may either splay your purple cards in the direction one of your other cards is
  //     splayed, or you may splay one of your other colors in the direction that your purple
  //     cards are splayed.

  public function initialExecution()
  {
    if (self::isEcho()) {
      $colors = [];
      for ($color = 0; $color < 5; $color++) {
        if (self::getSplayDirection($color) > 0) {
          $colors[] = $color;
        }
      }
      self::setAuxiliaryArray($colors); // Track which colors can be selected
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'hand',
        'score_keyword' => true,
        'color'         => self::getAuxiliaryArray(),
      ];
    } else {
      $choices = [];
      $purpleSplayDirection = self::getSplayDirection($this->game::PURPLE);
      foreach (self::getAllColorsOtherThan($this->game::PURPLE) as $color) {
        // The first 4 choices are for splaying purple in the direction of the other colors
        if (self::mayBeSplayedInDirection($this->game::PURPLE, self::getSplayDirection($color))) {
          $choices[] = self::getSplayDirection($color);
        }
        // The last 4 choices are for splaying the other colors in the direction of purple
        if (self::mayBeSplayedInDirection($color, $purpleSplayDirection)) {
          $choices[] = 5 + $color;
        }
      }
      return [
        'can_pass' => true,
        'choices'  => $choices,
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    $choiceMap = [];
    for ($splayDirection = 1; $splayDirection <= 4; $splayDirection++) {
      $choiceMap[$splayDirection] = [
        clienttranslate('Splay ${color} ${splay_direction}'),
        'i18n'            => ['color', 'splay_direction'],
        'color'           => $this->game->getColorInClear($this->game::PURPLE),
        'splay_direction' => $this->game->getSplayDirectionInClear($splayDirection),
      ];
    }
    $purpleSplayDirection = self::getSplayDirection($this->game::PURPLE);
    foreach (self::getAllColorsOtherThan($this->game::PURPLE) as $color) {
      $choiceMap[5 + $color] = [
        clienttranslate('Splay ${color} ${splay_direction}'),
        'i18n'            => ['color', 'splay_direction'],
        'color'           => $this->game->getColorInClear($color),
        'splay_direction' => $this->game->getSplayDirectionInClear($purpleSplayDirection),
      ];
    }
    return self::buildPromptFromList($choiceMap);
  }

  public function handleSpecialChoice(int $choice)
  {
    if ($choice <= 4) {
      self::splay($this->game::PURPLE, $choice);
    } else {
      self::splay($choice - 5, self::getSplayDirection($this->game::PURPLE));
    }
  }

  private function mayBeSplayedInDirection(int $color, int $splayDirection): bool
  {
    return $splayDirection > 0 && self::countCardsKeyedByColor('board')[$color] >= 2 && self::getSplayDirection($color) != $splayDirection;
  }

}