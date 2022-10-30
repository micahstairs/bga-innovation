<?php

namespace Unit\Innovation;

use Innovation\Cards;
use Innovation\Errors\CardNotFoundException;
use Innovation\Models\Card;
use Unit\BaseTest;

class CardsTest extends BaseTest
{
    protected Cards $cards;

    public function setUp(): void
    {
        parent::setUp();
        $this->cards = new Cards($this->table->getDbConnection());
    }

    public function testFindWhenCardExists()
    {
        $card = $this->cards->find(1);
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals(1, $card->getId());
    }

    public function testFindWhenCardDoesNotExist()
    {
        $this->expectException(CardNotFoundException::class);

        $card = $this->cards->find(9999999999);
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals(1, $card->getId());
    }
}
