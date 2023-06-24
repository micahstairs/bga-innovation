<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card548 extends Card
{

  // Safe Deposit Box:
  //   - You may choose to either draw and junk two [7], 
  //     or exchange all cards in your score pile with 
  //     all valued junked cards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
      // TODO: there is an error on the backend when this is executed
      return [
        'can_pass' => true,
        'choose_yes_or_no' => true
        ];
  }

  public function afterInteraction()
  {
  }

  public function getSpecialChoicePrompt(): array
  {
    $ageToDraw = $this->game->getAgeToDrawIn(self::getPlayerId(), 7);
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $ageToDraw <= $this->game->getMaxAge() ? clienttranslate('Draw and junk two ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($ageToDraw),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Exchange all cards in your score pile with all valued junked cards'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      $card1 = self::draw(7);
      $card2 = self::draw(7);
      self::transferCardFromTo($card1, 0, 'junk');
      self::transferCardFromTo($card2, 0, 'junk');
    } else {
      // "exchange all cards in your score pile with all valued junked cards."
      $score_cards = $this->game->getCardsInScore(self::getPlayerId());
      $junk_cards = $this->game->getCardsInLocation(self::getPlayerId(), 'junk');
      
      foreach($score_cards as $card) {
        self::transferCardFromTo($card, 0, 'junk');
      }
      
      foreach($score_cards as $card) {
        if ($card['age'] !== null) {
          self::transferCardFromTo($card, self::getPlayerId(), 'score');
        }
      }
      
      
    }
  }
  
}