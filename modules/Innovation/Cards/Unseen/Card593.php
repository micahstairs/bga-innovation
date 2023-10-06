<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card593 extends AbstractCard
{

  // Holography:
  //   - Choose red, blue, or green. Score all but your top five cards of that color, then splay it
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
      Colors::RED   => clienttranslate('Score all but top five red cards'),
      Colors::BLUE  => clienttranslate('Score all but top five blue cards'),
      Colors::GREEN => clienttranslate('Score all but top five green cards'),
    ]);
  }

  public function handleListChoice(int $color)
  {
    $stack = self::getStack($color);
    $scoredCard = false;
    for ($i = 0; $i < count($stack) - 5; $i++) {
      self::score($stack[$i]);
      $scoredCard = true;
    }

    if (self::splayAslant($color) && $scoredCard) {
      $lowestCardsInScorePile = $this->game->getIdsOfLowestCardsInLocation(self::getPlayerId(), 'score');
      $minScoreValue = self::getMinValueInLocation('score');
      foreach (self::getCards('achievements') as $card) {
        if (self::isValuedCard($card) && self::getValue($card) < $minScoreValue) {
          self::transferToScorePile($card);
        }
      }
      foreach ($lowestCardsInScorePile as $cardId) {
        self::achieve(self::getCard($cardId));
      }
    }
  }

}