<?php

namespace Unit\Innovation\Models;

use Innovation\Models\Card;
use Unit\BaseTest;

class CardTest extends BaseTest
{
    /**
     * @dataProvider providerTestInHand
     */
    public function testIsInHandWhenInHand(bool $expectedValue, string $locationValue)
    {
        $this->table
            ->setupNewGame()
            ->createGameInstanceForCurrentPlayer(66)
            ->stubActivePlayerId(66);
        $card = $this->buildCard([
            'location' => $locationValue,
        ]);
        $this->assertEquals($expectedValue, $card->isInHand());
    }
    public function providerTestInHand(): array
    {
        return [
            [true, 'hand'],
        ];
    }

    private function buildCard(array $data = []): Card
    {
        $data['id'] = (int)($data['id'] ?? rand(1, 100000));
        $data['type'] = (int)($data['type'] ?? 0);
        $data['age'] = (int)($data['age'] ?? 1);
        $data['faceup_age'] = (int)($data['faceup_age'] ?? 1);
        $data['color'] = (int)($data['color'] ?? 0);
        $data['spot_1'] = (int)($data['spot_1'] ?? 2);
        $data['spot_2'] = (int)($data['spot_2'] ?? 2);
        $data['spot_3'] = (int)($data['spot_3'] ?? 2);
        $data['spot_4'] = (int)($data['spot_4'] ?? -1);
        $data['spot_5'] = (int)($data['spot_5'] ?? -1);
        $data['spot_6'] = (int)($data['spot_6'] ?? -1);
        $data['dogma_icon'] = (int)($data['dogma_icon'] ?? 0);
        $data['has_demand'] = !empty($data['has_demand']);
        $data['is_relic'] = !empty($data['is_relic']);
        $data['owner'] = (int)($data['owner'] ?? 1);
        $data['location'] = empty($data['location']) ? '' : $data['location'];
        $data['position'] = (int)($data['position'] ?? 0);
        $data['splay_direction'] = (int)($data['splay_direction'] ?? 0);
        $data['selected'] = !empty($data['selected']);
        $data['icon_hash'] = (int)($data['icon_hash'] ?? 0);
        return new Card($data);
    }
}
