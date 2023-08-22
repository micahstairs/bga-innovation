<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card350 extends Card
{

  // Scissors
  // - 3rd edition:
  //   - ECHO: Take a bottom card from your board into your hand.
  //   - You may choose up to two cards from your hand. For each card chosen, either meld it or score it.
  //   - If Paper is a top card on any player's board, transfer it to your score pile.
  // - 4th edition:
  //   - ECHO: Score your bottom yellow card.
  //   - You may choose up to two cards from your hand. For each card chosen, either meld it or score it.
  //   - If Paper is a top card on any player's board, score it.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::setMaxSteps(1);
      } else {
        self::score(self::getBottomCardOfColor($this->game::YELLOW));
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      self::putPaperInScorePileIfTopCard();
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'board',
        'bottom_from'   => true,
        'location_to'   => 'hand',
      ];
    } else if (self::isFirstInteraction() || self::isThirdInteraction()) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'location_to'   => 'none',
      ];
    } else {
      return [
        'can_pass' => true,
        'choices'  => [1, 2],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isNonDemand()) {
      self::setAuxiliaryValue2($card['id']); // Tracks card to meld or score
      self::setMaxSteps(self::getMaxSteps() + 1);
    }
  }

  protected function getPromptForListChoice(): array
  {
    $card = self::getCard(self::getAuxiliaryValue2());
    $cardArgs = $this->game->getNotificationArgsForCardList([$card]);
    return self::buildPromptFromList([
      1 => [clienttranslate('Meld ${card}'), 'card' => $cardArgs],
      2 => [clienttranslate('Score ${card}'), 'card' => $cardArgs],
    ]);
  }

  public function handleSpecialChoice(int $choice)
  {
    $card = self::getCard(self::getAuxiliaryValue2());
    if ($choice === 1) {
      self::meld($card);
    } else {
      self::score($card);
    }
    if (self::isSecondInteraction() && self::countCards("hand") > 0) {
      self::setMaxSteps(self::getMaxSteps() + 1);
    }
  }

  private function putPaperInScorePileIfTopCard()
  {
    $paper = self::getCard(30);
    if ($this->game->isTopBoardCard($paper)) {
      if (self::isFirstOrThirdEdition()) {
        self::transferToScorePile($paper);
      } else {
        self::score($paper);
      }
      self::transferToScorePile($paper);
    }
  }

}