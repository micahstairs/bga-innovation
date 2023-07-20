<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card558 extends Card
{

  // Enigma Machine:
  //   - Choose to either safeguard all available standard achievements, transfer all your secrets
  //     to your hand, or transfer all cards in your hand to the available achievements.
  //   - Choose a color you have splayed left and splay it up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() == 1) {
      if (self::getCurrentStep() == 1) {
        return ['choices' => [1, 2, 3]];
      } else {
        return [
          'n'             => 'all',
          'location_from' => 'achievements',
          'owner_from'    => 0,
          'location_to'   => 'safe',
        ];
      }
    } else {
      return [
        'splay_direction'     => $this->game::UP,
        'has_splay_direction' => [$this->game::LEFT],
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      1 => clienttranslate('Safeguard all available standard achievements'),
      2 => clienttranslate('Transfer all your secrets to your hand'),
      3 => clienttranslate('Transfer all cards in your hand to the available achievements'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice == 1) {
      self::setMaxSteps(2);;
    } else if ($choice == 2) {
      foreach (self::getCards( 'safe') as $card) {
        self::putInHand($card);
      }
    } else if ($choice == 3) {
      foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
        $this->game->transferCardFromTo($card, 0, 'achievements');
      }
    }
  }

}