<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card569 extends Card
{

  // Area 51:
  //   - You may splay your green cards up.
  //   - Choose to either draw an [11], or safeguard an available standard achievement.
  //   - Reveal one of your secrets, and fully execute it if it is your turn.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    // No more code needs to be executed after the card has been self-executed
    if (self::getPostExecutionIndex() > 0) {
      return [];
    }

    if (self::getEffectNumber() == 1) {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => array($this->game::GREEN),
      ];
    } else if (self::getEffectNumber() == 2) {
      if (self::getCurrentStep() == 1) {
        return ['choices' => [1, 2]];
      } else {
        return [
          'owner_from'    => 0,
          'location_from' => 'achievements',
          'location_to'   => 'safe',
        ];
      }
    } else {
      return [
        'location_from' => 'safe',
        'location_to'   => 'revealed',
      ];
    }

  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() == 3) {
      if (self::getNumChosen() > 0) {
        if ($this->game->getActivePlayerId() == self::getPlayerId()) {
          self::fullyExecute(self::getLastSelectedCard());
        }
        self::putBackInSafe(self::getLastSelectedCard());
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList(
      [
        1 => clienttranslate('Draw an ${age}'),
        2 => clienttranslate('Safeguard an available achievement'),
      ],
      ['age' => $this->game->getAgeSquare(11)],
    );
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      self::draw(11);
    } else {
      self::setMaxSteps(2);
    }
  }

}