<?php

namespace app\controllers;

use app\models\forms\InstagramPostsSearch;
use InstagramScraper\Model\Media;
use yii\web\Controller;
use Yii;
use yii\web\Cookie;

/**
 * Контроллер для отображения постов Instagram.
 * 
 * Class InstagramPostsController
 * @package app\controllers
 */
class InstagramPostsController extends Controller
{
    /** @var string Настройки куки с параметрами поиска. */
    protected const SEARCH_COOKIE = [
        'KEY' => 'instagram_posts_search', //Ключ куки.
        'LIFETIME' => 365 * 24 * 60 * 60, //Время жизни в секундах.
    ];

    /**
     * Страница со списком постов Instagram.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        /** @var InstagramPostsSearch $search */
        $search = new InstagramPostsSearch();

        /** @var Media[] $posts */
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
        /** @var array $params */
        $params = Yii::$app->request->queryParams;

        if (empty($params)) {
            $params = Yii::$app->request->cookies->getValue(self::SEARCH_COOKIE['KEY'], []);
        }

        Yii::$app->response->cookies->add(new Cookie([
            'name' => self::SEARCH_COOKIE['KEY'],
            'value' => $params,
            'expire' => time() + self::SEARCH_COOKIE['LIFETIME'],
        ]));

        return $params;
    }
}
