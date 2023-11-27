<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card373_4E extends AbstractCard
{

  // Clock (4th edition):
  //   - ECHO: You may splay your color with the most cards right.
  //   - I DEMAND you transfer all cards of value equal to the number of visible cards of the color
  //     of my choice on my board from your hand and your score pile to my score pile! Junk an
  //     available achievement of that value!

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $counts = self::countCardsKeyedByColor(Locations::BOARD);
      $maxCount = max($counts);
      $colors = [];
      foreach (Colors::ALL as $color) {
        if ($counts[$color] == $maxCount) {
          $colors[] = $color;
        }
      }
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => $colors,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'player_id'    => self::getLauncherId(),
        'choose_color' => true,
      ];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    $value = self::countVisibleCardsInStack($color, self::getLauncherId());
    if ($value <= 11) {
      foreach (self::getCardsKeyedByValue(Locations::HAND)[$value] as $card) {
        self::transferToScorePile($card, self::getLauncherId());
      }
      foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
    self::setAuxiliaryValue($value); // Track value to junk
  }

}