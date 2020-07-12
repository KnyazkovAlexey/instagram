<?php

namespace app\services;

use InstagramScraper\Instagram;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Model\Media;
use Throwable;
use Yii;

/**
 * Сервис для получения постов Instagram.
 *
 * Class InstagramPostsService
 * @package app\services
 */
class InstagramPostsService
{
    /** @var string Настройки кэширования постов. */
    protected const CACHE = [
        'KEY_PREFIX' => 'instagram_last_posts.', //Префикс ключей.
        'LIFETIME' => 10 * 60, //Время жизни в секундах.
    ];

    /** @var Instagram $instagramApi Сервис для работы с API Instagram. */
    protected Instagram $instagramApi;

    /**
     * InstagramService constructor.
     */
    public function __construct()
    {
        /** Локально можно использовать $this->instagramApi = new Instagram(); и не логиниться. */
        $this->instagramApi = Instagram::withCredentials(
            Yii::$app->params['instagram']['username'],
            Yii::$app->params['instagram']['password'],
            new InstagramCache(),
        );

        $this->instagramApi->login();
    }

    /**
     * Получение последних постов указанных пользователей.
     *
     * @param string[] $logins Массив логинов Instagram.
     * @param int $postsCount Необходимое количество постов.
     * @return Media[]
     * @throws Throwable;
     */
    public function getLastPosts(array $logins = [], int $postsCount = 10): array
    {
        /** @var Media[] $posts */
        $posts = [];

        foreach ($logins as $login) {
            $posts = array_merge($posts, $this->getUserLastPosts($login, $postsCount));
        }

        /** Сортировка постов по дате создания. */
        usort($posts, function (Media $post1, Media $post2) {
            return $post2->getCreatedTime() <=> $post1->getCreatedTime();
        });

        return array_slice($posts, 0, $postsCount);
    }

    /**
     * Получение последних постов пользователя.
     *
     * @param string $login Логин Instagram.
     * @param int $postsCount Необходимое количество постов.
     * @return Media[]
     * @throws Throwable
     */
    protected function getUserLastPosts(string $login, int $postsCount): array
    {
        /** @var Media[]|false $userPosts */
        $userPosts = $this->getUserLastPostsCache($login);

        if (false !== $userPosts) {
            return $userPosts;
        }

        try {
            $userPosts = $this->instagramApi->getMedias($login, $postsCount);
        } catch (InstagramNotFoundException $e) {
            /** Ничего страшного, если аккаунт не нашёлся. */
            $userPosts = [];
        } catch (Throwable $e) {
            throw $e;
        }

        $this->setUserLastPostsCache($login, $userPosts);

        return $userPosts;
    }

    /**
     * Получение кэша последних постов пользователя.
     *
     * @param string $login Логин Instagram.
     * @return Media[]|false Вернёт false, если кэш не существует.
     */
    protected function getUserLastPostsCache(string $login)
    {
        return Yii::$app->cache->get($this->getUserLastPostsCacheKey($login));
    }

    /**
     * Кэширование последних постов пользователя.
     *
     * @param string $login Логин Instagram.
     * @param Media[] $userPosts Массив постов Instagram.
     * @return bool
     */
    protected function setUserLastPostsCache(string $login, array $userPosts): bool
    {
        return Yii::$app->cache->set($this->getUserLastPostsCacheKey($login), $userPosts, self::CACHE['LIFETIME']);
    }

    /**
     * Получение ключа для кэширования последних постов пользователя.
     *
     * @param string $login Логин Instagram.
     * @return string
     */
    protected function getUserLastPostsCacheKey(string $login): string
    {
        return self::CACHE['KEY_PREFIX'] . $login;
    }
}
