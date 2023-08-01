<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card385 extends Card
{

  // Bifocals
  // - 3rd edition
  //   - ECHO: Draw and foreshadow a card of any value.
  //   - You may return a card from your forecast. If you do, draw and foreshadow a card of equal
  //     value to the card returned.
  //   - You may splay your green cards right.
  // - 4th edition
  //   - ECHO: Return a card from your forecast.
  //   - Draw and foreshadow a [7], and then if Bifocals was foreseen, a card of value equal to the
  //     number of available special achievements.
  //   - You may splay your green cards right. If Bifocals was foreseen, splay any color of your
  //     cards up.

  public function initialExecution()
  {
    if (self::isFourthEdition() && self::isFirstNonDemand()) {
      self::drawAndForeshadow(7);
      if (self::wasForeseen()) {
        self::drawAndForeshadow(self::getNumberOfAvailableSpecialAchievements());
      }
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      self::setMaxSteps(self::wasForeseen() ? 2 : 1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::getThirdEditionInteractionOptions();
    } else {
      return self::getFourthEditionInteractionOptions();
    }
  }

  public function getThirdEditionInteractionOptions(): array
  {
    if (self::isEcho()) {
      return ['choose_value' => true];
    } else if (self::isFirstNonDemand()) {
      return [
        'can_pass'       => true,
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::GREEN],
      ];
    }
  }

  public function getFourthEditionInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else if (self::getCurrentStep() === 1) {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::GREEN],
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
      ];
    }
  }

  public function handleSpecialChoice($value)
  {
    self::drawAndForeshadow($value);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstOrThirdEdition() && self::isFirstNonDemand()) {
      self::drawAndForeshadow($card['age']);
    }
  }

  private function getNumberOfAvailableSpecialAchievements(): int
  {
    $count = 0;
    foreach (self::getCards('achievements', 0) as $achievement) {
      if (!self::isValuedCard($achievement['type'])) {
        $count++;
      }
    }
    return $count;
  }

}