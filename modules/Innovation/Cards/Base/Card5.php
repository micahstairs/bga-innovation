<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card5 extends AbstractCard
{
  //
  // - 3rd edition:
  //   - I DEMAND you transfer a card with a [PROSPERITY] from your hand to my score pile! If you
  //     do, draw a [1], and repeat this dogma effect!
  //   - If no cards were transferred due to this demand, draw a [1].
  // - 4th edition:
  //   - I DEMAND you transfer a card with [PROSPERITY] from your hand to my score pile! If you
  //     do, draw a [1], and repeat this effect!
  //   - If no cards were transferred due to this demand, draw a [1].

  public function initialExecution()
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition() && $this->game->innovationGameState->echoesExpansionEnabled()) {
        self::setMaxSteps(1);
      } else {
        // Automation is possible in most situations
        do {
          $cardWasTransferred = false;
          foreach (self::getCards(Locations::HAND) as $card) {
            if (self::hasIcon($card, Icons::PROSPERITY)) {
              self::transferToScorePile($card, self::getLauncherId());
              self::draw(1);
              $cardWasTransferred = true;
              self::setAuxiliaryValue(1); // Remember that a card was transferred
              break;
            }
          }
        } while ($cardWasTransferred);
        self::revealHand();
      }
    } else if (self::isFirstNonDemand()) {
      if (self::getAuxiliaryValue() <= 0) {
        self::draw(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from'    => Locations::HAND,
      'owner_to'         => self::getLauncherId(),
      'location_to'      => Locations::SCORE,
      'with_icon'        => Icons::PROSPERITY,
      'reveal_if_unable' => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::setAuxiliaryValue(1); // Remember that a card was transferred
    self::setNextStep(1);
  }

}