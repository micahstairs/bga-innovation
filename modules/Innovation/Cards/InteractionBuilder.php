<?php

namespace Innovation\Cards;

use Innovation\Enums\Directions;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;

/* Builder class for interactions */
class InteractionBuilder
{
  private array $interactionOptions = [];
  private int $launcherId;

  function __construct(int $launcherId)
  {
    $this->interactionOptions = [];
    $this->launcherId = $launcherId;
  }

  function build(): array
  {
    return $this->interactionOptions;
  }

  function canPass(bool $canPass): InteractionBuilder
  {
    $this->interactionOptions['can_pass'] = $canPass;
    return $this;
  }

  // NUMBER OF CARDS

  function exactly(int $n): InteractionBuilder
  {
    $this->interactionOptions['n'] = $n;
    return $this;
  }

  function anyNumber(): InteractionBuilder
  {
    $this->interactionOptions['n_min'] = 1;
    $this->interactionOptions['n_max'] = 'all';
    return $this;
  }

  function all(): InteractionBuilder
  {
    $this->interactionOptions['n'] = 'all';
    return $this;
  }

  function minCards(int $min): InteractionBuilder
  {
    $this->interactionOptions['n_min'] = $min;
    return $this;
  }

  function maxCards(int $max): InteractionBuilder
  {
    $this->interactionOptions['n_max'] = $max;
    return $this;
  }

  // VALUE OF CARDS

  function value(int $value): InteractionBuilder
  {
    $this->interactionOptions['age'] = $value;
    return $this;
  }

  function range(int $min, int $max): InteractionBuilder
  {
    $this->interactionOptions['age_min'] = $min;
    $this->interactionOptions['age_max'] = $max;
    return $this;
  }

  function lowest(): InteractionBuilder
  {
    $this->interactionOptions['age'] = ValueSelectors::LOWEST;
    return $this;
  }

  function highest(): InteractionBuilder
  {
    $this->interactionOptions['age'] = ValueSelectors::HIGHEST;
    return $this;
  }

  // ICONS ON CARDS

  function withIcon(string $icon): InteractionBuilder
  {
    $this->interactionOptions['icon'] = $icon;
    return $this;
  }

  // COLOR OF CARDS

  function withColor(array $colors): InteractionBuilder
  {
    $this->interactionOptions['color'] = $colors;
    return $this;
  }

  // DIRECTION OF SPLAY

  function splayLeft(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = [Directions::LEFT];
    return $this;
  }

  function splayRight(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = [Directions::RIGHT];
    return $this;
  }

  function splayUp(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = [Directions::UP];
    return $this;
  }

  function splayAslant(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = [Directions::ASLANT];
    return $this;
  }

  // SOURCE LOCATION

  function fromAvailableAchievements(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::AVAILABLE_ACHIEVEMENTS;
    return $this;
  }

  function fromBoard(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::BOARD;
    return $this;
  }

  function fromHand(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::HAND;
    return $this;
  }

  function fromScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    return $this;
  }

  function fromRevealedAndHand(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = 'revealed,hand';
    return $this;
  }

  // DESTINATION LOCATION

  function toMine(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->launcherId;
    $this->interactionOptions['location_to'] = $this->interactionOptions['location_from'];
    return $this;
  }

  function toMy(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->launcherId;
    return $this;
  }

  function toMyScore(): InteractionBuilder
  {
    return $this->toMy()->toScore();
  }

  function junk(): InteractionBuilder
  {
    $this->interactionOptions['junk_keyword'] = true;
    return $this;
  }

  function meld(): InteractionBuilder
  {
    $this->interactionOptions['meld_keyword'] = true;
    return $this;
  }

  function return(): InteractionBuilder
  {
    $this->interactionOptions['return_keyword'] = true;
    return $this;
  }

  function score(): InteractionBuilder
  {
    $this->interactionOptions['score_keyword'] = true;
    return $this;
  }

  function toScore(): InteractionBuilder
  {
    $this->interactionOptions['location_to'] = Locations::SCORE;
    return $this;
  }

  function tuck(): InteractionBuilder
  {
    $this->interactionOptions['tuck_keyword'] = true;
    return $this;
  }

  // SPECIAL CHOICES

  function choose(array $choices): InteractionBuilder
  {
    $this->interactionOptions['choices'] = $choices;
    return $this;
  }

  function choosePlayer(array $playerIds): InteractionBuilder
  {
    $this->interactionOptions['choose_player'] = true;
    $this->interactionOptions['players'] = $playerIds;
    return $this;
  }

  // EXTRA OPTIONS

  function revealIfUnable(): InteractionBuilder
  {
    $this->interactionOptions['reveal_if_unable'] = true;
    return $this;
  }

}