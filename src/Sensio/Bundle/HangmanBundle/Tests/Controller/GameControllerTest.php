<?php

namespace Sensio\Bundle\HangmanBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sensio\Bundle\HangmanBundle\Game\Game;

class GameControllerTest extends WebTestCase
{
    private $client;

    // Write your functional test scenario here...

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }

    protected function tearDown()
    {
        $this->client = null;

        parent::tearDown();
    }

    public function testTryWord()
    {
        $this->client->request('GET', '/game');
        $crawler = $this->playWord('php');

        $this->assertEquals(
            'Congratulations!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testGameOverHanged()
    {
        $this->client->request('GET', '/game/');
        $crawler = $this->playWord('foo');

        $this->assertEquals(
            'Game Over!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testGuessWord()
    {
        $this->client->request('GET', '/game/');

        foreach (array('H', 'X', 'P') as $letter) {
            $crawler = $this->playLetter($letter);
        }

        $this->assertEquals(
            'Congratulations!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testGetLetterAndGetHanged()
    {
        $this->client->request('GET', '/game/');

        for ($i = 1; $i <= Game::MAX_ATTEMPTS; $i++) {
            $crawler = $this->playLetter('Z');
        }

        $this->assertEquals(
            'Game Over!',
            $crawler->filter('#content > h2:first-child')->text()
        );
    }

    public function testGameReset()
    {
        $this->client->request('GET', '/game/');
        $crawler = $this->playLetter('P');

        $link = $crawler->selectLink('Reset the game')->link();
        $crawler = $this->client->click($link);

        $this->assertCount(0, $crawler->filter('.word_letters .guessed'));
        $this->assertCount(3, $crawler->filter('.word_letters .hidden'));
    }

    public function testGuessLetterWithoutStartedGame()
    {
        $this->client->request('GET', '/game/letter/H/');
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testGuessWordWithoutStartedGame()
    {
        $this->client->request('POST', '/game/word', array('word' => 'php'));
    }

    private function playWord($word)
    {
        $crawler = $this->client->getCrawler();

        $form = $crawler->selectButton('Let me guess...')->form();

        return $this->client->submit($form, array('word' => $word));
    }

    private function playLetter($letter)
    {
        $crawler = $this->client->getCrawler();

        $link = $crawler->selectLink($letter)->link();

        return $this->client->click($link);
    }

}
