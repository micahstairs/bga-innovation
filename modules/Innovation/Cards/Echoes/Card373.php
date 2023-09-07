<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;

class Card373 extends Card
{

  // Clock
  // - 3rd edition:
  //   - ECHO: You may splay your color with the most cards right.
  //   - I DEMAND you draw and reveal three [10], total the number of [EFFICIENCY] on them, and then
  //     return them! Transfer all cards of that value from your hand and score pile to my score
  //     pile!
  // - 4th edition:
  //   - ECHO: You may splay your color with the most cards right.
  //   - I DEMAND you transfer all cards of value equal to the number of visible cards of the color
  //     of my choice on my board from your hand and your score pile to my score pile! Junk an
  //     available achievement of that value!

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstOrThirdEdition()) {
      $count = 0;
      for ($i = 0; $i < 3; $i++) {
        $card = self::drawAndReveal(10);
        $count += $this->game->countIconsOnCard($card, $this->game::EFFICIENCY);
      }
      self::notifyAll(
        clienttranslate('There were a total of ${n} ${icon}.'),
        ['n' => $count, 'icon' => self::renderIcon($this->game::EFFICIENCY)]
      );
      self::setAuxiliaryValue($count); // Track which value will be transferred
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $counts = self::countCardsKeyedByColor('board');
      $maxCount = max($counts);
      $colors = [];
      for ($color = 0; $color < 5; $color++) {
        if ($counts[$color] == $maxCount) {
          $colors[] = $color;
        }
      }
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => $colors,
      ];
    } else if (self::isFirstOrThirdEdition()) {
      return [
        'n'              => 'all',
        'location_from'  => 'revealed',
        'return_keyword' => true,
      ];
    } else if (self::isFirstInteraction()) {
      $values = [];
      for ($color = 0; $color < 5; $color++) {
        $count = $this->game->countVisibleCards(self::getPlayerId(), $color);
        if (1 <= $count && $count <= 11) {
          $values[] = $count;
        }
      }
      return [
        'player_id'    => self::getLauncherId(),
        'choose_value' => true,
        'age'          => $values,
      ];
    } else {
      return [
        'location_from' => 'achievements',
        'owner_from'    => 0,
        'junk_keyword'  => true,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleSpecialChoice(int $value)
  {
    self::transferHandAndScorePileToLauncher($value);
    self::setAuxiliaryValue($value); // Track value to junk
    self::setMaxSteps(2);
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::isFirstOrThirdEdition()) {
      self::transferHandAndScorePileToLauncher(self::getAuxiliaryValue());
    }
  }

  private function transferHandAndScorePileToLauncher($value) {
    foreach (self::getCardsKeyedByValue('hand')[$value] as $card) {
      self::transferToScorePile($card, self::getLauncherId());
    }
    foreach (self::getCardsKeyedByValue('score')[$value] as $card) {
      self::transferToScorePile($card, self::getLauncherId());
    }
  }

}