<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card519 extends AbstractCard
{

  // Blackmail:
  //   - I DEMAND you reveal your hand! Meld a revealed card of my choice! Reveal your score pile!
  //     Self-execute a card revealed due to this effect of my choice, replacing 'may' with 'must'!

  public function initialExecution()
  {
    self::setAuxiliaryArray([]);
    foreach (self::getCards(Locations::HAND) as $card) {
      self::reveal($card);
      self::addToAuxiliaryArray(self::getId($card));
    }
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->fromYourRevealed()->ofMyChoice();
    } else {
      $choices = [];
      $array = self::getAuxiliaryArray();
      for ($i = 0; $i < count($array); $i++) {
        $choices[] = $i;
      }
      return self::youMust()->choose($choices)->ofMyChoice();
    }
  }

  protected function getPromptForListChoice(): array
  {
    $cardIds = self::getAuxiliaryArray();
    $choices = [];
    for ($i = 0; $i < count($cardIds); $i++) {
      $choices[$i] = [
        clienttranslate('Self-execute ${card}'),
        'card' => $this->game->getNotificationArgsForCardList([self::getCard($cardIds[$i])]),
      ];
    }
    return self::buildPromptFromList($choices);
  }

  public function handleListChoice(int $choice)
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    $cardId = self::getAuxiliaryArray()[$choice];
    $this->game->selfExecute(self::getCard($cardId), /*replace_may_with_must=*/ true);
  }

  public function afterInteraction()
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    foreach (self::getCards(Locations::REVEALED) as $card) {
      self::transferToHand($card);
    }
    self::revealScorePile();
    foreach (self::getCards(Locations::SCORE) as $card) {
      self::addToAuxiliaryArray(self::getId($card));
    }
  }

}