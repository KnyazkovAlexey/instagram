<?php

namespace app\services;

use InstagramScraper\Instagram;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Model\Media;
use Throwable;
use Yii;
use Iterator;

/**
 * Сервис для получения постов Instagram.
 */
class InstagramPostsService
{
    /** @var string Префикс ключей для кэша постов. */
    protected const CACHE_KEY_PREFIX = 'instagram_last_posts.';
    /** @var int Время жизни кэша постов (в секундах). */
    protected const CACHE_LIFETIME = 10 * 60;

    /** @var Instagram $instagramApi Сервис для парсинга сайта instagram.com */
    protected Instagram $instagramApi;

    public function __construct()
    {
        //Локально можно использовать $this->instagramApi = new Instagram(); и не логиниться.
        $this->instagramApi = Instagram::withCredentials(
            Yii::$app->params['instagram']['username'],
            Yii::$app->params['instagram']['password'],
            new InstagramCache(),
        );

        $this->instagramApi->login();
        $this->instagramApi->saveSession();
    }

    /**
     * Получение последних постов указанных пользователей.
     *
     * @param string[] $logins Массив логинов Instagram.
     * @param int $postsCount Необходимое количество постов.
     * @return Media[]|Iterator
     */
    public function getLastPosts(array $logins = [], int $postsCount = 10): Iterator
    {
        /** @var Iterator Используем кучу (вместо array_merge и usort), чтобы оптимизировать расход памяти */
        $posts = new InstagramPostsHeap($postsCount);

        foreach ($logins as $login) {
            foreach ($this->getUserLastPosts($login, $postsCount) as $post) {
                $posts->insert($post);
            }
        }

        return $posts;
    }

    /**
     * Получение последних постов пользователя.
     *
     * @param string $login Логин Instagram.
     * @param int $postsCount Необходимое количество постов.
     * @return Media[]
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
            //Ничего страшного, если аккаунт не нашёлся.
            $userPosts = [];
        } catch (Throwable $e) {
            //Всё остальное стоит логировать.
            Yii::error('Ошибка при парсинге постов юзера "' . $login . '". '. $e->getMessage());

            $userPosts = [];
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
        return Yii::$app->cache->get($this->getCacheKey($login));
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
        return Yii::$app->cache->set($this->getCacheKey($login), $userPosts, self::CACHE_LIFETIME);
    }

    /**
     * Получение ключа для кэширования последних постов пользователя.
     *
     * @param string $login Логин Instagram.
     * @return string
     */
    protected function getCacheKey(string $login): string
    {
        return self::CACHE_KEY_PREFIX . $login;
    }
}
