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
    if (self::getEffectNumber() == 1) {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => array($this->game::GREEN),
      ];
    } else if (self::getEffectNumber() == 2) {
      if (self::getCurrentStep() == 1) {
        return ['choose_yes_or_no' => true];
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
          // "and fully execute it if it is your turn."
          // TODO: Fully execute doesn't seem to work
          self::fullyExecute(self::getLastSelectedCard());
        }
        // put it back
        // TODO(4E): There's a bug here because we should ignore the safe limit in this case.
        $this->game->transferCardFromTo(self::getLastSelectedCard(), self::getPlayerId(), 'safe');
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    $ageToDraw = $this->game->getAgeToDrawIn(self::getPlayerId(), 11);
    $maxAge = $this->game->getMaxAge();
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $ageToDraw <= $maxAge ? clienttranslate('Draw an ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($ageToDraw)
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Safeguard an available achievement'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 0) {
      self::setMaxSteps(2);
    } else {
      self::draw(11);
    }
  }

}