<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card586 extends AbstractCard
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
    if (self::isFirstInteraction()) {
      return ['choices' => [1, 2]];
    } else {
      return [
        'location_from'  => 'safe',
        'return_keyword' => true,
      ];
    }
  }

  protected function getPromptForListChoice(): array
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

  public function handleListChoice($choice)
  {
    $args = ['side' => $this->getPrintableCoinSide($choice)];
    self::notifyPlayer(clienttranslate('${You} call ${side}.'), $args);
    self::notifyOthers(clienttranslate('${player_name} calls ${side}.'), $args);

    $coinFlip = bga_rand(1, 2);
    self::notifyAll(clienttranslate('The coin landed on ${side}.'), ['side' => $this->getPrintableCoinSide($coinFlip)]);

    if ($choice != $coinFlip) {
      if (self::isDemand() || self::countCards('safe') == 0) {
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

  public function handleCardChoice(array $card)
  {
    self::setNextStep(1);
    self::setMaxSteps(1);
  }

}