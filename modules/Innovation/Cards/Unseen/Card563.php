<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card563 extends Card
{

  // Joy Buzzer:
  //   - I DEMAND you exchange all cards in your hand with all the lowest cards in my hand!
  //   - You may score all the highest cards in your hand. If you do, score your top purple card.

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
      'choices'  => [1],
    ];
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([1 => clienttranslate('Score all the highest cards in your hand')]);
  }

  public function handleListChoice(int $choice) {
    if ($choice === 1) {
      foreach ($this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), 'hand') as $cardId) {
        self::score(self::getCard($cardId));
      }
      self::score(self::getTopCardOfColor(Colors::PURPLE));
    }
  }

}