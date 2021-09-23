<?php

namespace app\tests\unit\services\search;

use app\services\InstagramPostsHeap;
use app\tests\TestCase;
use InstagramScraper\Model\Media;

/**
 * Тестирование кучи для хранения постов Instagram.
 */
class InstagramPostsHeapTest extends TestCase
{
    public function testInsertAndExtract()
    {
        $posts = new InstagramPostsHeap(2);

        $this->assertCount(0, $posts);

        $time1 = strtotime('2021-01-02');
        $time2 = strtotime('2021-01-01');
        $time3 = strtotime('2021-01-03');
        $media1 = Media::create(['created_time' => $time1]);
        $media2 = Media::create(['created_time' => $time2]);
        $media3 = Media::create(['created_time' => $time3]);

        $posts->insert($media1);
        $posts->insert($media2);
        $this->assertCount(2, $posts);
        $this->assertEquals($time2, $posts->top()->getCreatedTime());

        $posts->insert($media3);
        $this->assertCount(2, $posts);
        $this->assertEquals($time1, $posts->top()->getCreatedTime());

        $this->assertInstanceOf(Media::class, $posts->extract());
        $this->assertCount(1, $posts);
        $this->assertEquals($time3, $posts->top()->getCreatedTime());

        $this->assertInstanceOf(Media::class, $posts->extract());
        $this->assertEmpty($posts);
    }
}