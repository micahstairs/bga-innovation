<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card549 extends AbstractCard
{

  // Black Market
  //   - You may safeguard a card from your hand. If you do, reveal two available standard
  //     achievements. You may meld a revealed card with no [EFFICIENCY] or [AVATAR]. Return each
  //     revealed card you do not meld.

  public function getInteractionOptions(): array
  {

    if (self::isFirstInteraction()) {
      return self::youMay()->safeguard()->fromYourHand()->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->reveal()->exactly(2)->fromAvailableAchievements()->build();
    } else if (self::isThirdInteraction()) {
      return self::youMay()->meld()->onlyCardsInAuxiliaryArray()->fromYourRevealed()->build();
    } else {
      return self::youMust()->return()->all()->fromYourRevealed()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() > 0) {
        $card = self::getLastSelectedCard();
        if ($card['location'] == 'safe' && $card['owner'] == self::getPlayerId()) {
          self::setMaxSteps(4);
        }
      }
    } else if (self::isSecondInteraction()) {
      $cardIds = self::getRevealedCardIdsWithoutEfficiencyOrAvatar();
      if (count($cardIds) > 0) {
        self::setAuxiliaryArray($cardIds);
      } else {
        self::setNextStep(4);
      }
    }
  }

  private function getRevealedCardIdsWithoutEfficiencyOrAvatar()
  {
    $cardIds = [];
    foreach (self::getCards('revealed') as $card) {
      if (!self::hasIcon($card, Icons::EFFICIENCY) && !self::hasIcon($card, Icons::AVATAR)) {
        $cardIds[] = self::getId($card);
      }
    }
    return $cardIds;
  }

}