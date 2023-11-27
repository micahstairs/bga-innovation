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
    spot_5: number | null;
    spot_6: number | null;
    dogma_icon: number;
    is_relic: boolean;
    has_demand: boolean;

    // From materials file
    name: string;
    condition_for_claiming: string;
    alternative_condition_for_claiming: string;
    echo_effect: string;
    i_demand_effect: string;
    i_compel_effect: string;
    non_demand_effect_1: string;
    non_demand_effect_2: string;
    non_demand_effect_3: string;
}

function parseCard(card: any): Card {
    return {
        id: parseInt(card.id),
        type: parseInt(card.type),
        age: parseInt(card.age),
        faceup_age: parseInt(card.faceup_age),
        color: parseInt(card.color),
        spot_1: parseInt(card.spot_1),
        spot_2: parseInt(card.spot_2),
        spot_3: parseInt(card.spot_3),
        spot_4: parseInt(card.spot_4),
        spot_5: card.spot_5 ? parseInt(card.spot_5) : null,
        spot_6: card.spot_6 ? parseInt(card.spot_6) : null,
        dogma_icon: parseInt(card.dogma_icon),
        is_relic: card.is_relic,
        name: card.name,
        has_demand: card.has_demand,
        condition_for_claiming: card.condition_for_claiming ?? null,
        alternative_condition_for_claiming: card.alternative_condition_for_claiming ?? null,
        echo_effect: card.echo_effect ?? null,
        i_demand_effect: card.i_demand_effect ?? null,
        i_compel_effect: card.i_compel_effect ?? null,
        non_demand_effect_1: card.non_demand_effect_1 ?? null,
        non_demand_effect_2: card.non_demand_effect_2 ?? null,
        non_demand_effect_3: card.non_demand_effect_3 ?? null
    };
}

function getHiddenIconsWhenSplayed(card: Card, direction: number): number[] {
    var icons: (number | null)[] = [];
    switch (direction) {
        case 1: // left
            icons = [card.spot_1, card.spot_2, card.spot_3, card.spot_6];
            break;
        case 2: // right
            icons = [card.spot_3, card.spot_4, card.spot_5, card.spot_6];
            break;
        case 3: // up
            icons = [card.spot_1, card.spot_5, card.spot_6];
            break;
        case 4: // aslant
            icons = [card.spot_5, card.spot_6];
            break;
        default: // unsplayed
            icons = getAllIcons(card);
            break;
    }
    return icons.filter(icon => icon !== null) as number[];
}

function getVisibleIconsWhenSplayed(card: Card, direction: number): number[] {
    var icons: (number | null)[] = [];
    switch (direction) {
        case 1: // left
            icons = [card.spot_4, card.spot_5];
            break;
        case 2: // right
            icons = [card.spot_1, card.spot_2];
            break;
        case 3: // up
            icons = [card.spot_2, card.spot_3, card.spot_4];
            break;
        case 4: // aslant
            icons = [card.spot_1, card.spot_2, card.spot_3, card.spot_4];
            break;
        default: // unsplayed
            return [];
    }
    return icons.filter(icon => icon !== null) as number[];
}

function getAllIcons(card: Card): number[] {
    return [card.spot_1, card.spot_2, card.spot_3, card.spot_4, card.spot_5, card.spot_6].filter(icon => icon !== null) as number[];
}

function getBonusIconValues(icons: number[]) {
    let bonus_values: number[] = [];
    icons.forEach(icon => {
        bonus_values.push(getBonusIconValue(icon));
    });
    return bonus_values;
}

function getBonusIconValue(icon: number) {
    if (icon > 100 && icon <= 112) {
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

function isMuseum(card_id: number) {
    return 1200 <= card_id && card_id <= 1204;
}