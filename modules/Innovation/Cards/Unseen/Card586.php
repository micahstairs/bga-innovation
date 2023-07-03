<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card586 extends Card
{

  // Quantum Computers:
  //   - I DEMAND you flip a coin! If you lose the flip, you lose!
  //   - Flip a coin. If you win the flip, this effect is complete. If you lose the flip, return
  //     one of your secrets. If you don't, you lose. Otherwise, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return ['choices' => [1, 2]];
    } else {
      return [
        'location_from' => 'safe',
        'location_to'   => 'deck',
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must call whether the coin will land heads or tails'),
      "message_for_others" => clienttranslate('${player_name} must call whether the coin will land heads or tails'),
      "options"            => self::getOptionsForChoiceFromList(
        [
          1 => clienttranslate('Heads'),
          2 => clienttranslate('Tails'),
        ]
      ),
    ];
  }

  public function handleSpecialChoice($choice)
  {
    $this->notifications->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} call ${side}.'), 
      ['You' => 'You', 'side' => $this->getPrintableCoinSide($choice)]
    );
    $this->notifications->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} calls ${side}.'),
      [
        'player_name' => $this->notifications->getColoredPlayerName(self::getPlayerId()),
        'side' => $this->getPrintableCoinSide($choice),
      ]
    );
    $coinFlip = bga_rand(1, 2);
    $this->notifications->notifyGeneralInfo(
      clienttranslate('The coin landed on ${side}.'),
      ['side' => $coinFlip == 1 ? clienttranslate('heads') : clienttranslate('tails')]
    );

    if ($choice != $coinFlip) {
      if (self::isDemand() || $this->game->countCardsInLocation(self::getPlayerId(), 'safe') == 0) {
        self::lose();
      } else {
        self::setMaxSteps(2);
      }
    }
  }

  private function getPrintableCoinSide(int $side): string
  {
    return $side == 1 ? clienttranslate('heads') : clienttranslate('tails');
  }

  public function handleCardChoice(array $card) {
    self::setNextStep(1);
    self::setMaxSteps(1);
  }

}