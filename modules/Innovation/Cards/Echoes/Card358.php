<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card358 extends Card
{

  // Katana
  // - 3rd edition:
  //   - I DEMAND you transfer two top cards with a [AUTHORITY] from your board to my score pile!
  //     If you transferred any, draw a card of value equal to the total number of [AUTHORITY] on
  //     those cards and transfer it to my forecast!
  // - 4th edition:
  //   - I DEMAND you transfer two top cards with a [AUTHORITY] from your board to my score pile!
  //     If you transfer exactly one and Katana was foreseen, junk all available standard
  //     achievements!

  public function initialExecution()
  {
    // In 3rd edition, this tracks the number of AUTHORITY icons transferred
    // In 4th edition, this tracks the number of cards transferred
    self::setAuxiliaryValue(0);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => 'board',
      'owner_to'      => self::getLauncherId(),
      'location_to'   => 'score',
      'with_icon'     => Icons::AUTHORITY,
    ];
  }

  public function handleCardChoice(array $card)
  {
    // Repeat the interaction if this was the first card that was transferred
    if (self::getAuxiliaryValue() === 0) {
      self::setMaxSteps(2);
    }

    if (self::isFirstOrThirdEdition()) {
      self::incrementAuxiliaryValue($this->game->countIconsOnCard($card, Icons::AUTHORITY));
    } else {
      self::incrementAuxiliaryValue(1);
    }
  }

  public function afterInteraction()
  {
    $auxiliaryValue = self::getAuxiliaryValue();

    if ($auxiliaryValue === 0 || self::isSecondInteraction()) {
      if (self::isFirstOrThirdEdition()) {
        if ($auxiliaryValue > 0) {
          $card = self::draw($auxiliaryValue);
          // TODO(4E): There shouldn't be a forecast keyword here.
          self::foreshadow($card, [$this, 'transferToHand'], self::getLauncherId());
        }
      } else {
        if ($auxiliaryValue === 1 && self::wasForeseen()) {
          foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
            if (self::isValuedCard($card)) {
              self::junk($card);
            }
          }
        }
      }
    }
  }

}