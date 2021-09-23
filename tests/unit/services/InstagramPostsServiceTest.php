<?php

namespace app\tests\unit\services\search;

use app\services\InstagramPostsService;
use app\tests\TestCase;
use InstagramScraper\Model\Media;

/**
 * Тестирование сервиса для получения постов Instagram.
 */
class InstagramPostsServiceTest extends TestCase
{
    /**
     * Успешный поиск.
     */
    public function testSuccess()
    {
        $logins = ['leomessi', 'cristiano'];

        $instagramPosts = (new InstagramPostsService())->getLastPosts($logins, 2);

        $this->assertCount(2, $instagramPosts);

        foreach ($instagramPosts as $instagramPost) {
            $this->assertInstanceOf(Media::class, $instagramPost);

            $this->assertContains($instagramPost->getOwner()->getUsername(), $logins);
        }
    }
}