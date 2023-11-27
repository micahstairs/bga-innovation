<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card521 extends AbstractCard
{

  // April Fool's Day:
  //   - Transfer a card from your hand or score pile to the board of the player on your right. If
  //     you don't, claim the Folklore achievement.
  //   - Splay your yellow cards right, and unsplay your purple cards, or vice versa.
  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'location_from' => Locations::HAND_OR_SCORE,
        'owner_to'      => $this->game->getActivePlayerIdOnRightOfActingPlayer(),
        'location_to'   => Locations::BOARD,
      ];
    } else {
      return ['choices' => [0, 1]];
    }
  }

  public function afterInteraction() {
    if (self::isFirstNonDemand() && self::getNumChosen() === 0) {
      self::claim(CardIds::FOLKLORE);
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => clienttranslate('Splay purple right and unsplay yellow'),
      1 => clienttranslate('Splay yellow right and unsplay purple'),
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    if ($choice === 1) {
      self::splayRight(Colors::YELLOW);
      self::unsplay(Colors::PURPLE);
    } else {
      self::splayRight(Colors::PURPLE);
      self::unsplay(Colors::YELLOW);
    }
  }

}