<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card401 extends Card
{

  // Elevator
  // - 3rd edition
  //   - ECHO: Score your top or bottom green card.
  //   - Choose a value present in your score pile. Choose to transfer all cards of the chosen value
  //     from either all other players' hands or all their score piles to your score pile.
  // - 4th edition
  //   - ECHO: Score your top or bottom green card.
  //   - Choose a value present in your score pile. Choose to score all cards of the chosen value
  //     from either all opponents' hands or all their score piles. Draw and foreshadow a card of
  //     the chosen value.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $topCard = self::getTopCardOfColor($this->game::GREEN);
      $bottomCard = self::getBottomCardOfColor($this->game::GREEN);
      self::setAuxiliaryArray([$topCard['id'], $bottomCard['id']]);
      return [
        'location_from'                   => 'pile',
        'score_keyword'                   => true,
        'color'                           => [$this->game::GREEN],
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'choose_value' => true,
        'age'          => self::getUniqueValues('score'),
      ];
    } else {
      return ['choices' => [1, 2]];
    }
  }

  protected function getPromptForListChoice(): array
  {
    $renderedValue = self::renderValue(self::getAuxiliaryValue());
    if (self::isFirstOrThirdEdition()) {
      return self::buildPromptFromList([
        1 => [clienttranslate('Transfer all ${age} from all other players\' hands to your score pile'), 'age' => $renderedValue],
        2 => [clienttranslate('Transfer all ${age} from all other players\' score piles to your score pile'), 'age' => $renderedValue],
      ]);
    } else {
      return self::buildPromptFromList([
        1 => [clienttranslate('Score ${age} from all opponents\' hands'), 'age' => $renderedValue],
        2 => [clienttranslate('Score ${age} from all opponents\' score piles'), 'age' => $renderedValue],
      ]);
    }
  }

  public function handleSpecialChoice($choice)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue($choice); // Track the value which will be taken from hands or score piles
      self::setMaxSteps(2);
    } else {
      $value = self::getAuxiliaryValue();
      $sourceLocation = $choice === 1 ? 'hand' : 'score';
      $playerIds = self::isFirstOrThirdEdition() ? self::getOtherPlayerIds() : self::getOpponentIds();
      foreach ($playerIds as $playerId) {
        foreach (self::getCardsKeyedByValue($sourceLocation, $playerId)[$value] as $card) {
          if (self::isFirstOrThirdEdition()) {
            self::transferToScorePile($card);
          } else {
            self::score($card);
          }
        }
      }
      if (self::isFourthEdition()) {
        self::drawAndForeshadow($value);
      }
    }
  }

}