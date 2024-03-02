<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card211 extends AbstractCard
{
  // Dolly the Sheep
  // - 3rd edition:
  //   - You may score your bottom yellow card. You may draw and tuck a [1]. If your bottom yellow
  //     card is Domestication, you win. Otherwise, meld the highest card in your hand, then draw
  //     an [10].
  // - 4th edition:
  //   - You may score your bottom yellow card. You may draw and tuck a [1]. If your bottom yellow
  //     card is Domestication, you win. Otherwise, meld the highest card in your hand, then draw
  //     an [11].
  //   - Junk all available achievements.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else if (self::isSecondNonDemand()) {
      self::junkCards(self::getCards(Locations::AVAILABLE_ACHIEVEMENTS));
      self::notifyPlayer(clienttranslate('${You} junk all available achievements.'));
      self::notifyOthers(clienttranslate('${player_name} junks all available achievements.'));
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'      => true,
        'location_from' => Locations::BOARD,
        'bottom_from'   => true,
        'color'         => [Colors::YELLOW],
        'score_keyword' => true,

      ];
    } else if (self::isSecondInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'meld_keyword'  => true,
        'age'           => self::getMaxValueInLocation(Locations::HAND),
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw and tuck a ${age}'), 'age' => self::renderValueWithType(1, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $choice)
  {
    self::drawAndTuck(1);
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $card = self::getBottomCardOfColor(Colors::YELLOW);
      if ($card && $card['id'] == CardIds::DOMESTICATION) {
        self::notifyPlayer(clienttranslate('${Your} bottom yellow card is Domestication.'));
        self::notifyOthers(clienttranslate('${player_name}\'s bottom yellow card is Domestication.'));
        self::win();
      } else {
        self::setMaxSteps(3);
      }
    } else if (self::isThirdInteraction()) {
      self::draw(11);
    }
  }

}