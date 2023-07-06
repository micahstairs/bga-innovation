<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card534 extends Card
{

  // Pen Name:
  //   - Choose to either splay an unsplayed non-purple color
  //     on your board left and self-execute its top card, or 
  //     meld a card from your hand and splay its color on your board right.

  public function initialExecution()
  {
    $top_cards = $this->game->getTopCardsOnBoard(self::getPlayerId());
    $cards_in_hand = $this->game->getCardsInHand(self::getPlayerId());

    $unsplayed_array = array();
    foreach ($top_cards as $card) {
        if ($card !== null && $card['splay_direction'] == 0 && $card['color'] != 4 && $this->game->countCardsInLocationKeyedByColor(self::getPlayerId(), 'board')[$card['color']] > 1) {
            $unsplayed_array[] = $card['color'];
        }
    }

    if (count($unsplayed_array) >= 1 && count($cards_in_hand) >= 1) {
        self::setMaxSteps(1);
        self::setAuxiliaryArray($unsplayed_array);
    } else if (count($unsplayed_array) == 0 && count($cards_in_hand) > 1) {
        self::setMaxSteps(3);
        self::setNextStep(3);
    } else if (count($cards_in_hand) == 0 && count($unsplayed_array) > 1) {
        self::setMaxSteps(2);
        self::setNextStep(2);
        self::setAuxiliaryArray($unsplayed_array);
    } else if (count($unsplayed_array) == 1) {
        self::splayLeft($unsplayed_array[0]);
        $top_card = self::getTopCardOfColor($unsplayed_array[0]);
        self::selfExecute($top_card);
    } else if (count($cards_in_hand) == 1) {
        $card = $cards_in_hand[0];
        self::meldCard($card);
        self::splayRight($card['color']);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'choose_yes_or_no' => true,
      ];
    } else if (self::getCurrentStep() == 2) {
      // "splay an unsplayed non-purple color on your 
      //  board left"
      return [
        'splay_direction' => $this->game::LEFT,
        'color' => $this->game->getAuxiliaryArray(),
      ];
    } else {
      // "or meld a card from your hand"
      return [
       'location_from' => 'hand',
       'location_to' => 'board',
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
        // Choice made decide which interaction to enable
        if (self::getAuxiliaryValue() == 1) {
            self::setMaxSteps(2);
        } else {
            self::setMaxSteps(3);
            self::setNextStep(3);
        }
    } else if (self::getCurrentStep() == 2) {
        if (self::getNumChosen() > 0) {
            $top_card = self::getTopCardOfColor(self::getLastSelectedColor());
            if ($top_card !== null) {
                self::selfExecute($top_card); // TODO: selfexecute doesn't work still
            }
        }
    } else {
        if (self::getNumChosen() > 0) {
            // meld happened, splay it right
            self::splayRight(self::getLastSelectedColor());                        
        }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Splay a non-purple pile left and self-execute the top card'),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Meld a card from hand and splay the pile right'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }
  
}