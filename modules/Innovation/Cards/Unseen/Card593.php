<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

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
    return ['choices' => [Colors::RED, Colors::BLUE, Colors::GREEN]];
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      Colors::RED   => clienttranslate('Score all but top four red cards'),
      Colors::BLUE  => clienttranslate('Score all but top four blue cards'),
      Colors::GREEN => clienttranslate('Score all but top four green cards'),
    ]);
  }

  public function handleListChoice(int $color)
  {
    $stack = self::getStack($color);
    $scoredCard = false;
    for ($i = 0; $i < count($stack) - 4; $i++) {
      self::score($stack[$i]);
      $scoredCard = true;
    }

    $splayedAslant = $this->game->getCurrentSplayDirection(self::getPlayerId(), $color) != Directions::ASLANT;
    self::splayAslant($color);

    if ($scoredCard && $splayedAslant) {
      $lowestCardsInScorePile = $this->game->getIdsOfLowestCardsInLocation(self::getPlayerId(), 'score');
      $minScoreValue = self::getMinValueInLocation('score');
      foreach (self::getCards('achievements') as $card) {
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