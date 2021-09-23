<?php

namespace app\controllers;

use app\models\search\InstagramPostsSearch;
use InstagramScraper\Model\Media;
use yii\web\Controller;
use Yii;
use yii\web\Cookie;

class InstagramPostsController extends Controller
{
    /** @var string Ключ куки с параметрами поиска. */
    protected const SEARCH_COOKIE_KEY = 'instagram_posts_search';
    /** @var string Время жизни куки с параметрами поиска (в секундах). */
    protected const SEARCH_COOKIE_LIFETIME = 365 * 24 * 60 * 60;

    /**
     * Страница со списком постов Instagram.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $search = new InstagramPostsSearch();

        $posts = $search->search($this->getSearchParams());

        return $this->render('index', compact('posts', 'search'));
    }

    /**
     * Получение параметров поиска.
     *
     * @return array
     */
    protected function getSearchParams(): array
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params)) {
            $params = Yii::$app->request->cookies->getValue(self::SEARCH_COOKIE_KEY, []);
        }

        Yii::$app->response->cookies->add(new Cookie([
            'name' => self::SEARCH_COOKIE_KEY,
            'value' => $params,
            'expire' => time() + self::SEARCH_COOKIE_LIFETIME,
        ]));

        return $params;
    }
}
