<?php

namespace app\tests\unit\models\search;

use app\models\search\InstagramPostsSearch;
use app\tests\TestCase;

/**
 * Тестирование модели для поиска постов Instagram.
 *
 * Class InstagramPostsSearchTest
 * @package app\tests\unit\models\search
 */
class InstagramPostsSearchTest extends TestCase
{
    /**
     * Успешная валидация.
     */
    public function testSuccess()
    {
        /** @var InstagramPostsSearch $search */
        $search = new InstagramPostsSearch([
            'loginsStr' => implode(',', $this->faker->words),
        ]);

        $this->assertTrue($search->validate());
    }

    /**
     * Очистка строки с логинами.
     */
    public function testSanitizing()
    {
        /** @var InstagramPostsSearch $search */
        $search = new InstagramPostsSearch([
            'loginsStr' => '    leomessi, cristiano,leomessi,  ',
        ]);

        $this->assertTrue($search->sanitize());

        $this->assertEquals($search->loginsStr, 'leomessi,cristiano');
        $this->assertCount(2, $search->logins);
        $this->assertContains('leomessi', $search->logins);
        $this->assertContains('cristiano', $search->logins);
    }

    /**
     * Пустая строка с логинами.
     */
    public function testEmptyLogins()
    {
        /** @var InstagramPostsSearch $search */
        $search = new InstagramPostsSearch([
            'loginsStr' => '',
        ]);

        $this->assertFalse($search->validate());
    }

    /**
     * Недопустимые символы в строке с логинами.
     */
    public function testWrongChars()
    {
        /** @var InstagramPostsSearch $search */
        $search = new InstagramPostsSearch([
            'loginsStr' => '<!!!>',
        ]);

        $this->assertFalse($search->validate());
    }

    /**
     * Недопустимая длина строки с логинами.
     */
    public function testWrongLength()
    {
        /** @var string $loginsStr */
        $loginsStr = str_repeat($this->faker->lexify('?????????,'), 16);

        /** @var InstagramPostsSearch $search */
        $search = new InstagramPostsSearch([
            'loginsStr' => $loginsStr,
        ]);

        $this->assertFalse($search->validate());
    }
}