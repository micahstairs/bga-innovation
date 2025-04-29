<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card82 extends AbstractCard
{
  // Skyscrapers:
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-yellow card with a [EFFICIENCY] from your board to my
  //     board! If you do, score the card beneath it, and return all other cards from that pile!
  // - 4th edition:
  //   - I DEMAND you transfer a top non-yellow card with [EFFICIENCY] from your board to mine! If
  //     you do, score your top card of that color, then return all cards of that color form your
  //     board, and transfer Skyscrapers to my hand if it is a top card!

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->non(Colors::YELLOW)->withIcon(Icons::EFFICIENCY)->fromYourBoard()->toMine()->build();
    } else {
      return self::youMust()->return()->all()->fromYourStack(self::getAuxiliaryValue())->build();
    }
  }

  public function handleCardChoice($card)
  {
    if (self::isFirstInteraction()) {
      $color = self::getColor($card);
      self::score(self::getTopCardOfColor($color));
      if (self::getTopCardOfColor($color)) {
        self::setMaxSteps(2);
        self::setAuxiliaryValue($color); // Remember the color for the second interaction
      } else {
        self::returnSkyscrapersIfNeeded();
      }
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::isSecondInteraction()) {
      $this->returnSkyscrapersIfNeeded();
    }
  }

  private function returnSkyscrapersIfNeeded(): void
  {
    if (self::isFourthEdition()) {
      $skyscrapersCard = $this->game->getIfTopCardOnBoard(CardIds::SKYSCRAPERS);
      if ($skyscrapersCard) {
        self::transferToHand($skyscrapersCard, self::getLauncherId());
      }
    }
  }

  public function demandMightBeEffective(): bool
  {
    $nonYellowTopCards = self::filterByColor(self::getTopCards(), Colors::NON_YELLOW);
    return count(self::filterByIcon($nonYellowTopCards, Icons::EFFICIENCY)) > 0;
  }

}