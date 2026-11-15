<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card116 extends AbstractCard
{

  // Priest-King
  // - 3rd edition:
  //   - Score a card from your hand. If you have a top card matching its color, execute each of
  //     the top card's non-demand dogma effects. Do not share them.
  //   - Claim an achievement, if eligible.
  // - 4th edition:
  //   - Score a card from your hand. If you have a top card matching its color, super-execute
  //     that top card it is your turn, otherwise self-execute it.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->revealAndScore()->fromYourHand();
    } else {
      return self::youMust()->achieveIfEligible();
    }
  }

  public function handleCardChoice(array $card)
  {
    $topCard = self::getTopCardOfColor(self::getColor($card));
    $super = self::isFourthEdition() && self::isTheirTurn();
    error_log(sprintf(
      'Priest-King handleCardChoice player=%s scored=%s color=%s top=%s mode=%s launcher=%s',
      self::getPlayerId(),
      $card['id'] ?? 'none',
      $card['color'] ?? 'none',
      $topCard['id'] ?? 'none',
      $super ? 'super' : 'self',
      self::getLauncherId()
    ));
    try {
      if ($super) {
        self::superExecute($topCard);
      } else {
        self::selfExecute($topCard);
      }
    } catch (\EndOfGame $e) {
      throw $e;
    } catch (\Exception $e) {
      error_log(sprintf(
        'Priest-King %sExecute failed scored=%s top=%s: %s in %s:%s',
        $super ? 'super' : 'self',
        $card['id'] ?? 'none',
        $topCard['id'] ?? 'none',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
      ));
      throw $e;
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFirstOrThirdEdition() && count($this->game->getClaimableStandardAchievementValues(self::getPlayerId())) > 0) {
      return true;
    }
    return self::hasCards(Locations::HAND);
  }

}