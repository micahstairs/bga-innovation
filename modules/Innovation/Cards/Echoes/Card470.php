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

  public function getSpecialChoicePrompt(): array
  {
    $cardId = self::getAuxiliaryValue();
    $cardArgs = $this->game->getNotificationArgsForCardList([self::getCard($cardId)]);
    return self::getPromptForChoiceFromList([
      1 => [clienttranslate('Achieve ${card} if eligible'), 'card' => $cardArgs],
      2 => [clienttranslate('Score ${card}'), 'card' => $cardArgs],
    ]);
  }

  public function handleSpecialChoice(int $choice)
  {
    $cardId = self::getAuxiliaryValue();
    $card = self::getCard($cardId);
    if ($choice === 2) {
      self::score($card);
      self::repeatIfForeseen();
    } else if (in_array($card['age'], $this->game->getClaimableValuesIgnoringAvailability(self::getPlayerId()))) {
      self::achieve($card);
      self::repeatIfForeseen();
    }
  }

  private function repeatIfForeseen()
  {
    if (self::wasForeseen()) {
      self::setNextStep(1);
      self::setMaxSteps(1);
    }
  }

}