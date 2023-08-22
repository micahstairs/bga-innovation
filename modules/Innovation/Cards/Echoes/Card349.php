<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card349 extends Card
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
    if (self::isEcho() || self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else {
      $minValue = null;
      foreach (self::getTopCards() as $card) {
        if ($card['color'] != $this->game::GREEN && ($minValue === null || $minValue > $card['faceup_age'])) {
          $minValue = $card['faceup_age'];
        }
      }
      if ($minValue === null) {
        $minValue = 0;
      }
      self::drawAndForeshadow($minValue + 3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      $options = [
        'location_from' => 'hand',
        'score_keyword' => true,
      ];
      if (self::isFirstOrThirdEdition()) {
        $options['with_bonus'] = true;
      } else {
        $options['type'] = self::getAllTypesOtherThan($this->game::BASE);
      }
      return $options;
    } else {
      return ['choices' => [2, 3]];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      2 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(2, $this->game::BASE)],
      3 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(3, $this->game::BASE)],
    ]);
  }

  public function handleSpecialChoice(int $value)
  {
    self::junkBaseDeck($value);
  }

  public function afterInteraction()
  {
    if (self::isEcho() && self::isFirstOrThirdEdition() && self::getNumChosen() === 0) {
      // Prove that the player has no cards with bonuses in hand.
      self::revealHand();
    }
  }

}