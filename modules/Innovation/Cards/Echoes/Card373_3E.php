<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card373_3E extends AbstractCard
{

  // Clock (3rd edition):
  //   - ECHO: You may splay your color with the most cards right.
  //   - I DEMAND you draw and reveal three [10], total the number of [EFFICIENCY] on them, and then
  //     return them! Transfer all cards of that value from your hand and score pile to my score
  //     pile!

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isDemand()) {
      $count = 0;
      for ($i = 0; $i < 3; $i++) {
        $card = self::drawAndReveal(10);
        $count += $this->game->countIconsOnCard($card, Icons::EFFICIENCY);
      }
      $args = ['n' => $count, 'icon' => Icons::render(Icons::EFFICIENCY)];
      self::notifyAll(clienttranslate('There were a total of ${n} ${icon}.'), $args);
      self::setAuxiliaryValue($count); // Track which value will be transferred
      self::setMaxSteps(1);
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
    } else {
      return [
        'n'              => 'all',
        'location_from'  => Locations::REVEALED,
        'return_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      $value = self::getAuxiliaryValue();
      if ($value <= 11) {
        foreach (self::getCardsKeyedByValue(Locations::HAND)[$value] as $card) {
          self::transferToScorePile($card, self::getLauncherId());
        }
        foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
          self::transferToScorePile($card, self::getLauncherId());
        }
      }
    }
  }

}