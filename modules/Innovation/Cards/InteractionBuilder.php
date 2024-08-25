<?php

namespace Innovation\Cards;

use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;
use Innovation\Cards\ExecutionState;

/* Builder class for interactions */
class InteractionBuilder
{
  private array $interactionOptions = [];
  private ExecutionState $state;

  function __construct(ExecutionState $state)
  {
    $this->interactionOptions = [];
    $this->state = $state;
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

  function withoutIcon(string $icon): InteractionBuilder
  {
    $this->interactionOptions['without_icon'] = $icon;
    return $this;
  }

  // COLOR OF CARDS

  function withColor(int|array $colors): InteractionBuilder
  {
    if (!is_array($colors)) {
      $colors = [$colors];
    }
    $this->interactionOptions['color'] = $colors;
    return $this;
  }

  function non(int $color): InteractionBuilder
  {
    $this->interactionOptions['color'] = Colors::getAllColorsOtherThan($color);
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

  function fromYourBoard(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::BOARD;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromMyBoard(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::BOARD;
    $this->interactionOptions['owner_from'] = $this->state->getLauncherId();
    return $this;
  }

  function fromYourHand(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::HAND;
    return $this;
  }

  function fromYourScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    return $this;
  }

  function fromMyScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    $this->interactionOptions['owner_from'] = $this->state->getLauncherId();
    return $this;
  }

  function fromMyHandOrRevealed(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = 'revealed,hand';
    return $this;
  }

  // DESTINATION LOCATION

  function toPlayer(int $playerId): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $playerId;
    return $this;
  }

  function toYours(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->state->getPlayerId();
    return $this;
  }

  function toMine(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->state->getLauncherId();
    $this->interactionOptions['location_to'] = $this->interactionOptions['location_from'];
    return $this;
  }

  function toMy(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->state->getLauncherId();
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

  function revealAndScore(): InteractionBuilder
  {
    $this->interactionOptions['location_to'] = Locations::REVEALED_THEN_SCORE;
    $this->interactionOptions['score_keyword'] = true;
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