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

  function ofMyChoice(): InteractionBuilder
  {
    $this->interactionOptions['player_id'] = $this->state->getLauncherId();
    return $this;
  }

  function otherThan(int $cardId): InteractionBuilder
  {
    $this->interactionOptions['not_id'] = $cardId;
    return $this;
  }

  function onlyCardsInAuxiliaryArray(): InteractionBuilder
  {
    $this->interactionOptions['card_ids_are_in_auxiliary_array'] = true;
    return $this;
  }

  function withDemandEffect(): InteractionBuilder
  {
    $this->interactionOptions['has_demand_effect'] = true;
    return $this;
  }

  function ofType(int|array $types): InteractionBuilder
  {
    if (!is_array($types)) {
      $types = [$types];
    }
    $this->interactionOptions['type'] = $types;
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

  function minValue(int $min): InteractionBuilder
  {
    $this->interactionOptions['age_min'] = $min;
    return $this;
  }

  function maxValue(int $max): InteractionBuilder
  {
    $this->interactionOptions['age_max'] = $max;
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
    $this->interactionOptions['with_icon'] = $icon;
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

  function currentlyUnsplayed(): InteractionBuilder
  {
    $this->interactionOptions['has_splay_direction'] = [Directions::UNSPLAYED];
    return $this;
  }

  function currentlySplayed(array $directions = Directions::SPLAYED): InteractionBuilder
  {
    $this->interactionOptions['has_splay_direction'] = $directions;
    return $this;
  }

  function currentlySplayedRight(): InteractionBuilder
  {
    $this->interactionOptions['has_splay_direction'] = [Directions::RIGHT];
    return $this;
  }

  // DIRECTION OF SPLAY

  function splayLeft(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = Directions::LEFT;
    return $this;
  }

  function splayRight(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = Directions::RIGHT;
    return $this;
  }

  function splayUp(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = Directions::UP;
    return $this;
  }

  function splayAslant(): InteractionBuilder
  {
    $this->interactionOptions['splay_direction'] = Directions::ASLANT;
    return $this;
  }

  // SOURCE LOCATION

  function fromAvailableAchievements(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::AVAILABLE_ACHIEVEMENTS;
    return $this;
  }

  function fromYourAchievements(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::ACHIEVEMENTS;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromYourRevealed(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::REVEALED;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromYourRevealedAndScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::REVEALED_THEN_SCORE; // Read as "REVEALED_AND_SCORE" here
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromYourStack(int $color): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::PILE;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    $this->interactionOptions['color'] = [$color];
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

  function fromAnyBoard(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::BOARD;
    $this->interactionOptions['owner_from'] = 'any player';
    return $this;
  }

  function fromOpponentsBoard(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::BOARD;
    $this->interactionOptions['owner_from'] = 'any opponent';
    return $this;
  }

  function fromYourHand(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::HAND;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromYourHandOrScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::HAND_OR_SCORE;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromMyHand(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::HAND;
    $this->interactionOptions['owner_from'] = $this->state->getLauncherId();
    return $this;
  }

  function fromYourScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromMyScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    $this->interactionOptions['owner_from'] = $this->state->getLauncherId();
    return $this;
  }

  function fromOpponentsScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    $this->interactionOptions['owner_from'] = 'any opponent';
    return $this;
  }

  function fromAnyScore(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = Locations::SCORE;
    $this->interactionOptions['owner_from'] = 'any player';
    return $this;
  }

  function fromYourHandOrRevealed(): InteractionBuilder
  {
    $this->interactionOptions['location_from'] = 'revealed,hand';
    $this->interactionOptions['owner_from'] = $this->state->getPlayerId();
    return $this;
  }

  function fromAnyPlayer(): InteractionBuilder
  {
    $this->interactionOptions['owner_from'] = 'any player';
    return $this;
  }

  function fromAnyOtherPlayer(): InteractionBuilder
  {
    $this->interactionOptions['owner_from'] = 'any other player';
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

  function toMyBoard(): InteractionBuilder
  {
    return $this->toMy()->toBoard();
  }

  function toMyScore(): InteractionBuilder
  {
    return $this->toMy()->toScore();
  }

  function toYourScore(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->state->getPlayerId();
    $this->interactionOptions['location_to'] = Locations::SCORE;
    return $this;
  }

  function toMyHand(): InteractionBuilder
  {
    return $this->toMy()->toHand();
  }

  function toYourHand(): InteractionBuilder
  {
    $this->interactionOptions['owner_to'] = $this->state->getPlayerId();
    $this->interactionOptions['location_to'] = Locations::HAND;
    return $this;
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

  function toBoard(): InteractionBuilder
  {
    $this->interactionOptions['location_to'] = Locations::BOARD;
    return $this;
  }

  function toHand(): InteractionBuilder
  {
    $this->interactionOptions['location_to'] = Locations::HAND;
    return $this;
  }

  function return(): InteractionBuilder
  {
    $this->interactionOptions['return_keyword'] = true;
    return $this;
  }

  function topDeck(): InteractionBuilder
  {
    $this->interactionOptions['topdeck_keyword'] = true;
    return $this;
  }

  function reveal(): InteractionBuilder
  {
    $this->interactionOptions['location_to'] = Locations::REVEALED;
    return $this;
  }

  function revealAndReturn(): InteractionBuilder
  {
    $this->interactionOptions['location_to'] = Locations::REVEALED_THEN_DECK;
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

  function achieve(): InteractionBuilder
  {
    $this->interactionOptions['achieve_keyword'] = true;
    return $this;
  }

  function achieveIfEligible(): InteractionBuilder
  {
    $this->interactionOptions['achieve_if_eligible'] = true;
    return $this;
  }

  function includingSpecialAchievements(): InteractionBuilder
  {
    $this->interactionOptions['include_special_achievements'] = true;
    return $this;
  }

  // SPECIAL CHOICES

  function chooseCardFrom(string $location): InteractionBuilder
  {
    $this->interactionOptions['choose_from'] = $location;
    return $this;
  }

  function choose(array $choices): InteractionBuilder
  {
    $this->interactionOptions['choices'] = $choices;
    return $this;
  }

  function chooseColor(?array $colors = null): InteractionBuilder
  {
    $this->interactionOptions['choose_color'] = true;
    if ($colors !== null) {
      $this->interactionOptions['color'] = $colors;
    }
    return $this;
  }

  function chooseType(): InteractionBuilder
  {
    $this->interactionOptions['choose_type'] = true;
    return $this;
  }

  function choosePlayer(?array $playerIndexes = null): InteractionBuilder
  {
    $this->interactionOptions['choose_player'] = true;
    if ($playerIndexes !== null) {
      $this->interactionOptions['players'] = $playerIndexes;
    }
    return $this;
  }

  function chooseValue(?array $values = null): InteractionBuilder
  {
    $this->interactionOptions['choose_value'] = true;
    if ($values !== null) {
      $this->interactionOptions['age'] = $values;
    }
    return $this;
  }

  function chooseNonNegativeInteger(): InteractionBuilder
  {
    $this->interactionOptions['choose_non_negative_integer'] = true;
    return $this;
  }

  function chooseTwoColors(): InteractionBuilder
  {
    $this->interactionOptions['choose_two_colors'] = true;
    return $this;
  }

  function chooseThreeColors(): InteractionBuilder
  {
    $this->interactionOptions['choose_three_colors'] = true;
    return $this;
  }

  function chooseIcon(array $icons): InteractionBuilder
  {
    $this->interactionOptions['choose_icon'] = true;
    $this->interactionOptions['icon'] = $icons;
    return $this;
  }

  function chooseToRearrange(): InteractionBuilder
  {
    $this->interactionOptions['choose_rearrange'] = true;
    return $this;
  }

  function chooseSpecialAchievement(): InteractionBuilder
  {
    $this->interactionOptions['choose_special_achievement'] = true;
    return $this;
  }

  // EXTRA OPTIONS

  function refreshingSelection(): InteractionBuilder
  {
    $this->interactionOptions['refresh_selection'] = true;
    return $this;
  }

  function revealingIfUnable(): InteractionBuilder
  {
    $this->interactionOptions['reveal_if_unable'] = true;
    return $this;
  }

  function withoutAutoselection(): InteractionBuilder
  {
    $this->interactionOptions['enable_autoselection'] = false;
    return $this;
  }

  function forceAutoselection(): InteractionBuilder
  {
    $this->interactionOptions['enable_autoselection'] = true;
    return $this;
  }

}