<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card401 extends AbstractCard
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

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $topCard = self::getTopCardOfColor(Colors::GREEN);
      $bottomCard = self::getBottomCardOfColor(Colors::GREEN);
      if ($topCard) {
        self::setAuxiliaryArray([self::getId($topCard), self::getId($bottomCard)]);
      } else {
        self::setAuxiliaryArray([]);
      }
      return self::youMust()->score()->onlyCardsInAuxiliaryArray()->fromYourStack(Colors::GREEN)->build();
    } else if (self::isFirstInteraction()) {
      $values = self::getUniqueValuesInLocation('score');
      return self::youMust()->chooseValue($values)->build();
    } else {
      return self::youMust()->choose([1, 2])->build();
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

  public function handleValueChoice($value)
  {
    self::setAuxiliaryValue($value); // Track the value which will be taken from hands or score piles
    self::setMaxSteps(2);
  }

  public function handleListChoice(int $choice)
  {
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