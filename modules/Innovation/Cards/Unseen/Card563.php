<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card563 extends AbstractCard
{

  // Joy Buzzer:
  //   - I DEMAND you exchange all cards in your hand with all the lowest cards in my hand!
  //   - You may choose a value and score all the cards in your hand of that value. If you do,
  //     score your top purple card.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $cardsInPlayerHand = self::getCards('hand');
      $lowestCardIdsInLauncherHand = $this->game->getIdsOfLowestCardsInLocation(self::getLauncherId(), 'hand');
      foreach ($cardsInPlayerHand as $card) {
        self::transferToHand($card, self::getLauncherId());
      }
      foreach ($lowestCardIdsInLauncherHand as $cardId) {
        self::transferToHand(self::getCard($cardId));
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass' => true,
      'choose_value'  => true,
    ];
  }

  public function handleValueChoice(int $value) {
    $didScore = false;
    foreach (self::getCardsKeyedByValue(Locations::SCORE)[$value] as $card) {
      self::score($card);
      $didScore = true;
    }
    if ($didScore) {
      self::score(self::getTopCardOfColor(Colors::PURPLE));
    }
  }

}