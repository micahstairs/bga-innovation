<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;
use Innovation\Enums\Colors;

class Card66 extends AbstractCard
{
  // Publications:
  // - 3rd edition:
  //   - You may rearrange the order of one color of cards on your board.
  //   - You may splay your yellow or blue cards up.
  // - 4th edition:
  //   - You may splay your yellow or blue cards up.
  //   - You may junk an available special achievement or make a special achievement in the junk available.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition() && self::isFirstNonDemand()) {
      if (self::canRearrange() > 1) {
        self::setMaxSteps(1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstOrThirdEdition() && self::isFirstNonDemand()) {
      return self::youMay()->chooseToRearrange();
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      return self::youMay()->chooseSpecialAchievement();
    } else {
      return self::youMay()->splayUp([Colors::BLUE, Colors::YELLOW]);
    }
  }

  private function canRearrange(): bool
  {
    $cardsByColor = self::getCardsKeyedByColor(Locations::BOARD);
    $maxCardsInSingleColor = max(array_map('count', $cardsByColor));
    return $maxCardsInSingleColor > 1;
  }

  protected function getPromptForRearrangeChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may rearrange one color of your cards. Click on a card then use arrows to move it within the pile'),
      "message_for_others" => clienttranslate('${player_name} may rearrange one color of his cards'),
    ];
  }

  protected function handleRearrangeChoice(int $colorId)
  {
    // Already done outside of this class
  }

  protected function getPromptForSpecialAchievementChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may junk an available special achievement or make a junked special achievement available'),
      "message_for_others" => clienttranslate('${player_name} may junk an available special achievement or make a junked special achievement available'),
    ];
  }

  protected function handleSpecialAchievementChoice(int $specialAchievementId)
  {
    $specialAchievement = self::getCard($specialAchievementId);
    if (self::getLocation($specialAchievement) == Locations::JUNK) {
      self::transferToAvailableAchievements($specialAchievement);
    } else {
      self::junk($specialAchievement);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFirstOrThirdEdition()) {
      return self::canRearrange() || self::canSplay([Colors::BLUE, Colors::YELLOW]);
    } else {
      return self::hasAvailableOrJunkedSpecialAchievements() || self::canSplay([Colors::BLUE, Colors::YELLOW]);
    }
  }

  private function hasAvailableOrJunkedSpecialAchievements()
  {
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
      if (self::isSpecialAchievement($card)) {
        return true;
      }
    }
    foreach (self::getCards(Locations::JUNK) as $card) {
      if (self::isSpecialAchievement($card)) {
        return true;
      }
    }
    return false;
  }

}