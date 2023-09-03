<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card470 extends Card
{

  // Streaming
  //   - Choose a color on your board. Choose to either achieve the top card of that color on your
  //     board, if eligible, or score it. If you do either, and Streaming was foreseen, repeat
  //     this effect using the same color.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_from' => 'board'];
    } else {
      return ['choices' => [1, 2]];
    }
  }

  public function handleCardChoice(array $card)
  {
    self::setAuxiliaryValue($card['id']); // Track card to achieve or score
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

  public function handleSpecialChoice(int $choice)
  {
    $card = self::getCard(self::getAuxiliaryValue());
    if ($choice === 2) {
      self::score($card);
      self::repeatIfForeseen($card['color']);
    } else if (in_array($card['age'], $this->game->getClaimableValuesIgnoringAvailability(self::getPlayerId()))) {
      self::achieve($card);
      self::repeatIfForeseen($card['color']);
    }
  }

  private function repeatIfForeseen(int $color)
  {
    $topCard = self::getTopCardOfColor($color);
    if ($topCard && self::wasForeseen()) {
      self::setAuxiliaryValue($topCard['id']);
      self::setNextStep(2);
      self::setMaxSteps(2);
    }
  }

}