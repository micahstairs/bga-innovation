declare type Card = {

    // From SQL database
    id: number;
    type: number;
    age: number;
    faceup_age: number;
    color: number;
    spot_1: number;
    spot_2: number;
    spot_3: number;
    spot_4: number;
    spot_5: number;
    spot_6: number;
    dogma_icon: number;
    is_relic: boolean;

    // From materials file
    name: string;
    condition_for_claiming: string;
    alternative_condition_for_claiming: string;
    echo_effect_1: string;
    i_demand_effect_1: string;
    i_demand_effect_1_is_compel: boolean;
    non_demand_effect_1: string;
    non_demand_effect_2: string;
    non_demand_effect_3: string;
}

function getHiddenIconsWhenSplayed(card: Card, direction: number): number[] {
    switch (direction) {
        case 1: // left
            return [card.spot_1, card.spot_2, card.spot_3, card.spot_6];
        case 2: // right
            return [card.spot_3, card.spot_4, card.spot_5, card.spot_6];
        case 3: // up
            return [card.spot_1, card.spot_5, card.spot_6];
        case 4: // aslant
            return [card.spot_5, card.spot_6];
        default: // unsplayed
            return getAllIcons(card);
    }
}

function getVisibleIconsWhenSplayed(card: Card, direction: number): number[] {
    switch (direction) {
        case 1: // left
            return [card.spot_4, card.spot_5];
        case 2: // right
            return [card.spot_1, card.spot_2];
        case 3: // up
            return [card.spot_2, card.spot_3, card.spot_4];
        case 4: // aslant
            return [card.spot_1, card.spot_2, card.spot_3, card.spot_4];
        default: // unsplayed
            return [];
    }
}

function getAllIcons(card: Card): number[] {
    return [card.spot_1, card.spot_2, card.spot_3, card.spot_4, card.spot_5, card.spot_6];
}

function getBonusIconValues(icons: number[]) {
    let bonus_values: number[] = [];
    icons.forEach(icon => {
        bonus_values.push(getBonusIconValue(icon));
    });
    return bonus_values;
}

function getBonusIconValue(icon: number) {
    // TODO(4E): If there is a bonus icon with a value higher than 11, then this needs to be changed.
    if (icon > 100 && icon <= 111) {
        return icon - 100;
    }
    return 0;
}

function countMatchingIcons(icons: number[], iconToMatch: number) {
    let count = 0;
    icons.forEach(icon => {
        if (icon == iconToMatch) {
            count++;
        }
    });
    return count;
}

function isFlag(card_id: number) {
    return 1000 <= card_id && card_id <= 1099;
}

function isFountain(card_id: number) {
    return 1100 <= card_id && card_id <= 1199;
}