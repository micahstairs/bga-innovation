<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card193 extends AbstractCard
{
  // Garland's Ruby Slippers
  // - 3rd edition:
  //   - Meld an [8] from your hand. If the melded card has no effects, you win. Otherwise, execute
  //     the effects of the melded card as if they were on this card. Do not share them.
  // - 4th edition:
  //   - Meld an [8] from your hand. If the melded card has no effects, you win. Otherwise,
  //     self-execute it.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::HAND,
      'meld_keyword'  => true,
      'age'           => 8,
    ];
  }

  public function handleCardChoice(array $card)
  {
    if ($card['dogma_icon'] === null || (self::isFirstOrThirdEdition() && $card['type'] == CardTypes::CITIES)) {
      self::notifyPlayer(clienttranslate('${You} melded a card with no effects.'));
      self::notifyOthers(clienttranslate('${player_name} melded a card with no effects.'));
      self::win();
    } else if (self::isFirstOrThirdEdition()) {
      self::fullyExecute($card);
    } else if (self::isFourthEdition()) {
      self::selfExecute($card);
    }
  }

}