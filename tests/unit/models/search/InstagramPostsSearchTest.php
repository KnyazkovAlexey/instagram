<?php

namespace app\tests\unit\models\search;

use app\models\search\InstagramPostsSearch;
use app\tests\TestCase;

/**
 * Тестирование модели для поиска постов Instagram.
 */
class InstagramPostsSearchTest extends TestCase
{
    /**
     * Успешная валидация.
     */
    public function testSuccess()
    {
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
        $loginsStr = str_repeat($this->faker->lexify('?????????,'), 16);

        $search = new InstagramPostsSearch([
            'loginsStr' => $loginsStr,
        ]);

        $this->assertFalse($search->validate());
    }
}