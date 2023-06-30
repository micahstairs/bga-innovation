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
      if (self::getEffectNumber() == 1) {
          self::setMaxSteps(1);
      } else if (self::getEffectNumber() == 2) {
          self::setMaxSteps(1);
      } else {
          self::setMaxSteps(1);
      }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() == 1) {
        // "You may splay your green cards up."
        return [
          'can_pass'        => true,
          'splay_direction' => $this->game::UP,
          'color'           => array($this->game::GREEN),
        ];
    } else if (self::getEffectNumber() == 2) {
        if (self::getCurrentStep() == 1) {
            // "Choose to either draw an [11], or safeguard an available standard achievement."
          return [
            'choose_yes_or_no' => true,
          ];
        } else {
            // "safeguard an available standard achievement."
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
            $this->game->transferCardFromTo(self::getLastSelectedCard(), self::getPlayerId(), 'safe');
        }
      }
  }  
  
  public function getSpecialChoicePrompt(): array
  {
    $player_id = self::getPlayerId();
    $age_to_draw = $this->game->getAgeToDrawIn($player_id, 11);
    $max_age = $this->game->getMaxAge();
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $age_to_draw <= $max_age ? clienttranslate('Draw an ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($age_to_draw)
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