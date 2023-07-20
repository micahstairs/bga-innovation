<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card593 extends Card
{

  // Holography:
  //   - Choose red, blue, or green. Score all but your top four cards of that color, then splay it
  //     aslant. If you do both, exchange all lowest cards in your score pile with all your
  //     claimed standard achievements of lower value.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return ['choices' => [$this->game::RED, $this->game::BLUE, $this->game::GREEN]];
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      $this->game::RED => clienttranslate('Score all but top four red cards'),
      $this->game::BLUE => clienttranslate('Score all but top four blue cards'),
      $this->game::GREEN => clienttranslate('Score all but top four green cards'),
    ]);
  }

  public function handleSpecialChoice(int $color)
  {
    $cards = self::getCardsKeyedByColor('board')[$color];
    $scoredCard = false;
    for ($i = 0; $i < count($cards) - 4; $i++) {
      self::score($cards[$i]);
      $scoredCard = true;
    }

    $splayedAslant = $this->game->getCurrentSplayDirection(self::getPlayerId(), $color) != $this->game::ASLANT;
    self::splayAslant($color);

    if ($scoredCard && $splayedAslant) {
      $lowestCardsInScorePile = $this->game->getIdsOfLowestCardsInLocation(self::getPlayerId(), 'score');
      $minScoreValue = self::getMinValueInLocation('score');
      foreach (self::getCards( 'achievements') as $card) {
        if (self::isValuedCard($card) && $card['age'] < $minScoreValue) {
          self::transferToScorePile($card);
        }
      }
      foreach ($lowestCardsInScorePile as $cardId) {
        self::achieve(self::getCard($cardId));
      }
    }
  }

}