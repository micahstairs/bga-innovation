<?php

namespace Innovation\Models;

/**
 * Represents a Card in the database.
 */
class Card
{
    const SPLAY_NONE = 0;
    const SPLAY_LEFT = 1;
    const SPLAY_RIGHT = 2;
    const SPLAY_UP = 3;
    const SPLAY_NOT_ON_BOARD = null;
    const LOCATION_HAND = 'hand';

    private int $id;
    private int $type;
    private int $age;
    private int $faceUpAge;
    private int $color;
    private int $spot1;
    private int $spot2;
    private int $spot3;
    private int $spot4;
    private int $spot5;
    private int $spot6;
    private int $dogmaIcon;
    private bool $hasDemand;
    private bool $isRelic;
    private int $ownerId;
    private string $location;
    private int $position;
    private int $splayDirection;
    private bool $selected;
    private int $iconHash;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->type = (int)($data['type'] ?? 0);
        $this->age = (int)($data['age'] ?? 0);
        $this->faceUpAge = (int)($data['faceup_age'] ?? 0);
        $this->color = (int)($data['color'] ?? 0);
        $this->spot1 = (int)($data['spot_1'] ?? 0);
        $this->spot2 = (int)($data['spot_2'] ?? 0);
        $this->spot3 = (int)($data['spot_3'] ?? 0);
        $this->spot4 = (int)($data['spot_4'] ?? 0);
        $this->spot5 = (int)($data['spot_5'] ?? 0);
        $this->spot6 = (int)($data['spot_6'] ?? 0);
        $this->dogmaIcon = (int)($data['dogma_icon'] ?? 0);
        $this->hasDemand = !empty($data['has_demand']);
        $this->isRelic = !empty($data['is_relic']);
        $this->ownerId = (int)($data['owner']);
        $this->location = (string)($data['location'] ?? '');
        $this->position = (int)($data['position'] ?? 0);
        $this->splayDirection = (int)($data['splay_direction'] ?? 0);
        $this->selected = !empty($data['selected']);
        $this->iconHash = (int)($data['icon_hash'] ?? 0);
    }

    /**
     * @return bool True if the card is splayed left
     */
    public function isSplayedLeft(): bool
    {
        return $this->getSplayDirection() == self::SPLAY_LEFT;
    }

    /**
     * @return bool True if the card is splayed right
     */
    public function isSplayedRight(): bool
    {
        return $this->getSplayDirection() == self::SPLAY_RIGHT;
    }

    /**
     * @return bool True if the card is splayed up
     */
    public function isSplayedUp(): bool
    {
        return $this->getSplayDirection() == self::SPLAY_UP;
    }

    /**
     * @return bool True if the card is not splayed
     */
    public function isNotSplayed(): bool
    {
        return $this->getSplayDirection() == self::SPLAY_NONE;
    }

    /**
     * Is the passed player the owner of this card?
     *
     * @param int $playerId
     * @return bool
     */
    public function isOwner(int $playerId): bool
    {
        return $this->getOwnerId() == $playerId;
    }

    /**
     * Is this card in the player's hand?
     *
     * @return bool
     */
    public function isInHand(): bool
    {
        return $this->getLocation() == self::LOCATION_HAND;
    }

    /*******************************************************************************************************************
     * GETTER METHODS
     ******************************************************************************************************************/

    /**
     * 0-104 for base cards, 105-109 for base special achievements, 110-214 for artifact cards, 215-219 for relics
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     *
     * 0 for base, 1 for artifacts, 2 for cities, 3 for echoes, 4 for figures
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * 1 to 10, NULL for a special achievement
     *
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * The same as age, except Battleship Yamato is an 11 instead of 8 (dynamically populated)
     *
     * @return int
     */
    public function getFaceUpAge(): int
    {
        return $this->faceUpAge;
    }

    /**
     * 0 (blue), 1 (red), 2 (green), 3 (yellow), 4 (purple) or NULL for a special achievement
     *
     * @return int
     */
    public function getColor(): int
    {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getSpot1(): int
    {
        return $this->spot1;
    }

    /**
     * @return int
     */
    public function getSpot2(): int
    {
        return $this->spot2;
    }

    /**
     * @return int
     */
    public function getSpot3(): int
    {
        return $this->spot3;
    }

    /**
     * @return int
     */
    public function getSpot4(): int
    {
        return $this->spot4;
    }

    /**
     * @return int
     */
    public function getSpot5(): int
    {
        return $this->spot5;
    }

    /**
     * @return int
     */
    public function getSpot6(): int
    {
        return $this->spot6;
    }

    /**
     * Feature icon for dogma, 1 (crown), 2 (leaf), 3 (bulb), 4 (tower), 5 (factory), 6 (clock) or NULL for no
     * icon (e.g. special achievement)
     *
     * @return int
     */
    public function getDogmaIcon(): int
    {
        return $this->dogmaIcon;
    }

    /**
     * Whether the card has at least one demand effect (will be populated using data in material.inc.php file)
     *
     * @return bool
     */
    public function isHasDemand(): bool
    {
        return $this->hasDemand;
    }

    /**
     * Whether or not the card is a relic
     *
     * @return bool
     */
    public function isRelic(): bool
    {
        return $this->isRelic;
    }

    /**
     * Id of the player who owns the card or 0 if no owner
     *
     * @return int
     */
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    /**
     * Hand, board, score, achievements, deck, display or revealed (achievements can be used both with owner = 0
     * (available achievement) or with a player as owner (the player has earned that achievement)
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Position in the given location. Bottom is zero (last card in deck), top is max. For hands, the cards are sorted
     * by age before being sorted by position. For boards, the positions reflect the order in the stacks, 0 for the
     * bottom card, maximum for active card.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Direction of the splay, 0 (no-splay), 1 (left), 2 (right), 3 (up) OR NULL if this card is not on board
     *
     * @return int
     */
    public function getSplayDirection(): int
    {
        return $this->splayDirection;
    }

    /**
     * Temporary flag to indicate whether the card is selected by its owner or not
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * A column that is updated on game start with a calculated hash of the card icons. This is for icon comparison
     * purposes regardless of the icon position.
     *
     * @return int
     */
    public function getIconHash(): int
    {
        return $this->iconHash;
    }
}
