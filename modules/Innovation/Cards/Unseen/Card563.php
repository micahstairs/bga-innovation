<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card563 extends Card
{

  // Joy Buzzer:
  //   - I DEMAND you exchange all cards in your hand with all the lowest cards in my hand!
  //   - You may score all the highest cards in your hand. If you do, score your top purple card.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $cardsInPlayerHand = $this->game->getCardsInHand(self::getPlayerId());
      $lowestCardIdsInLauncherHand = $this->game->getIdsOfLowestCardsInLocation(self::getLauncherId(), 'hand');
      foreach ($cardsInPlayerHand as $card) {
        self::putInHand($card, self::getLauncherId());
      }
      foreach ($lowestCardIdsInLauncherHand as $cardId) {
        self::putInHand(self::getCard($cardId));
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

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may score all the highest cards in his hand'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Score all the highest cards in your hand'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice) {
    if ($choice == 1) {
      foreach ($this->game->getIdsOfHighestCardsInLocation(self::getPlayerId(), 'hand') as $cardId) {
        self::score(self::getCard($cardId));
      }
      self::score(self::getTopCardOfColor($this->game::PURPLE));
    }
  }

}