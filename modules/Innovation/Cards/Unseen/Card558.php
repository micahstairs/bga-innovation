<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card558 extends AbstractCard
{

  // Enigma Machine:
  //   - Choose to either safeguard all available standard achievements, transfer all your secrets
  //     to your hand, or transfer all cards in your hand to the available achievements.
  //   - Choose a color you have splayed left and splay it up.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::getEffectNumber() === 1) {
      if (self::isFirstInteraction()) {
        return self::youMust()->choose([1, 2, 3]);
      } else {
        return self::youMust()->safeguard()->all()->fromAvailableAchievements();
      }
    } else {
      return self::youMust()->splayUp()->currentlySplayedLeft();
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Safeguard all available standard achievements'),
      2 => clienttranslate('Transfer all your secrets to your hand'),
      3 => clienttranslate('Transfer all cards in your hand to the available achievements'),
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      self::setMaxSteps(2);
    } else if ($choice === 2) {
      foreach (self::getCards('safe') as $card) {
        self::transferToHand($card);
      }
    } else if ($choice === 3) {
      foreach (self::getCards('hand') as $card) {
        $this->game->transferCardFromTo($card, 0, 'achievements');
      }
    }
  }

}