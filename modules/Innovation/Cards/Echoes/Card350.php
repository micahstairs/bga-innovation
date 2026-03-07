<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card350 extends AbstractCard
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
        self::score(self::getBottomCardOfColor(Colors::YELLOW));
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      self::putPaperInScorePileIfTopCard();
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMust()->fromYourBoard()->fromBottom()->toYourHand();
    } else if (self::isFirstInteraction() || self::isThirdInteraction()) {
      return self::youMay()->chooseCardFrom(Locations::HAND);
    } else {
      return self::youMay()->choose([1, 2]);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isNonDemand()) {
      self::setAuxiliaryValue2(self::getId($card)); // Tracks card to meld or score
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

  public function handleListChoice(int $choice)
  {
    $card = self::getCard(self::getAuxiliaryValue2());
    if ($choice === 1) {
      self::meld($card);
    } else {
      self::score($card);
    }
    if (self::isSecondInteraction() && self::countCards(Locations::HAND) > 0) {
      self::setMaxSteps(self::getMaxSteps() + 1);
    }
  }

  private function putPaperInScorePileIfTopCard()
  {
    $card = self::getCard(CardIds::PAPER);
    if ($this->game->isTopBoardCard($card)) {
      if (self::isFirstOrThirdEdition()) {
        self::transferToScorePile($card);
      } else {
        self::score($card);
      }
      self::transferToScorePile($card);
    }
  }

}