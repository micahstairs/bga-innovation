<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card470 extends AbstractCard
{

  // Streaming
  //   - Choose a color on your board. Choose to either achieve the top card of that color on your
  //     board, if eligible, or score it. If you do either, and Streaming was foreseen, repeat
  //     this effect using the same color.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseCardFrom('board')->build();
    } else {
      return self::youMust()->choose([1, 2])->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::setAuxiliaryValue(self::getId($card)); // Track card to achieve or score
    self::setMaxSteps(2);
  }

  protected function getPromptForListChoice(): array
  {
    $cardId = self::getAuxiliaryValue();
    $cardArgs = $this->game->getNotificationArgsForCardList([self::getCard($cardId)]);
    return self::buildPromptFromList([
      1 => [clienttranslate('Achieve ${card} if eligible'), 'card' => $cardArgs],
      2 => [clienttranslate('Score ${card}'), 'card' => $cardArgs],
    ]);
  }

  public function handleListChoice(int $choice)
  {
    $card = self::getCard(self::getAuxiliaryValue());
    if ($choice === 2) {
      self::score($card);
      self::repeatIfForeseen(self::getColor($card));
    } else if (in_array(self::getValue($card), $this->game->getClaimableValuesIgnoringAvailability(self::getPlayerId()))) {
      self::achieve($card);
      self::repeatIfForeseen(self::getColor($card));
    }
  }

  private function repeatIfForeseen(int $color)
  {
    $topCard = self::getTopCardOfColor($color);
    if ($topCard && self::wasForeseen()) {
      self::setAuxiliaryValue(self::getId($topCard));
      self::setNextStep(2);
      self::setMaxSteps(2);
    }
  }

}