<?php

namespace Unit;

use BaseTest;

class GameInfosTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->config = $this->loadGameInfo();
    }

    public function testPublisher()
    {
        $this->assertEquals('Asmadi Games', $this->config['publisher']);
    }

    public function testPublisherWebsite()
    {
        $this->assertEquals('https://asmadigames.com/', $this->config['publisher_website']);
    }

    public function testPublisherBggId()
    {
        $this->assertEquals(5407, $this->config['publisher_bgg_id']);
    }

    public function testBggId()
    {
        $this->assertEquals(63888, $this->config['bgg_id']);
    }

    public function testPlayers()
    {
        $this->assertEquals([2, 3, 4, 5], $this->config['players']);
    }
}
