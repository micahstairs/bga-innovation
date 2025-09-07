<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;

class Card349 extends AbstractCard
{

  // Glassblowing
  // - 3rd edition:
  //   - ECHO: Score a card with a bonus from your hand.
  //   - Draw and foreshadow a card of value three higher than the lowest non-green top card on your board.
  // - 4th edition:
  //   - ECHO: Score an expansion card from your hand.
  //   - Draw and foreshadow a card of value three higher than the lowest non-green top card on your board.
  //   - Choose [2] or [3]. Junk all cards in that deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $minValue = null;
      foreach (self::getTopCards() as $card) {
        if (self::getColor($card) != Colors::GREEN && ($minValue === null || $minValue > self::getFaceupValue($card))) {
          $minValue = self::getFaceupValue($card);
        }
      }
      if ($minValue === null) {
        $minValue = 0;
      }
      self::drawAndForeshadow($minValue + 3);
    } else if (self::getBaseDeckCount(2) > 0 || self::getBaseDeckCount(3) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        return self::youMust()->score()->withBonus()->fromYourHand()->revealingIfUnable()->build();
      } else {
        $types = CardTypes::getAllTypesOtherThan(CardTypes::BASE);
        return self::youMust()->score()->withTypes($types)->fromYourHand()->build();
      }
    } else {
      return self::youMust()->choose([2, 3])->build();
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      2 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(2, CardTypes::BASE)],
      3 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(3, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $value)
  {
    self::junkBaseDeck($value);
  }

}