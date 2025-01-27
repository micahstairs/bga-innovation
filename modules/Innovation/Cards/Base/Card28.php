<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card28 extends AbstractCard
{
  // Optics:
  // - 3rd edition:
  //   - Draw and meld a [3]. If it has a [PROSPERITY], draw and score a [4]. Otherwise, transfer a
  //     card from your score pile to the score pile of an opponent with fewer points than you.
  // - 4th edition:
  //   - Draw and meld a [3]. If it has [PROSPERITY], draw and score a [4]. Otherwise, transfer a card
  //     from your score pile to the score pile of an opponent with fewer points than you.

  public function initialExecution()
  {
    $card = self::drawAndMeld(3);
    if (self::hasIcon($card, Icons::PROSPERITY)) {
      self::drawAndScore(4);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choosePlayer(self::getOpponentsWithFewerPoints())->build();
    } else {
      return self::youMust()->fromYourScore()->toPlayer(self::getAuxiliaryValue())->toScore()->build();
    }
  }

  private function getOpponentsWithFewerPoints(): array
  {
    $playerScore = self::getScore();
    $opponentIndexes = [];
    foreach (self::getOpponentIds() as $opponentId) {
      if (self::getScore($opponentId) < $playerScore) {
        $opponentIndexes[] = $this->game->playerIdToPlayerIndex($opponentId);
      }
    }
    return $opponentIndexes;
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId); // Remember the chosen opponent
    self::setMaxSteps(2);
  }

}