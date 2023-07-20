<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

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
      'owner_to' => self::getLauncherId(),
      'location_to' => 'score',
      'with_icon' => $this->game::AUTHORITY,
    ];
  }

  public function handleCardChoice(array $card)
  {
    // Repeat the interaction if this was the first card that was transferred
    if (self::getAuxiliaryValue() === 0) {
      self::setMaxSteps(2);
    }

    if (self::isFirstOrThirdEdition()) {
      self::setAuxiliaryValue(self::getAuxiliaryValue() + $this->game->countIconsOnCard($card, $this->game::AUTHORITY));
    } else {
      self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
    }
  }

  public function afterInteraction() {
    $auxiliaryValue = self::getAuxiliaryValue();

    if ($auxiliaryValue === 0 || self::getCurrentStep() === 2) {
      if (self::isFirstOrThirdEdition()) {
        if ($auxiliaryValue > 0) {
          $card = self::draw($auxiliaryValue);
          self::foreshadow($card, self::getLauncherId());
        }
      } else {
        if ($auxiliaryValue === 1 && self::wasForeseen()) {
          foreach (self::getCards('achievements', 0) as $card) {
            if (self::isValuedCard($card)) {
              self::junk($card);
            }
          }
        }
      }
    }
  }

}