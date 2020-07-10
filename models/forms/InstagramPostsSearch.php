<?php

namespace app\models\forms;

use app\services\InstagramPostsService;
use InstagramScraper\Model\Media;
use yii\base\Model;
use Throwable;
use Yii;

/**
 * Форма для фильтрации списка постов Instagram.
 *
 * Class InstagramPostsSearch
 * @package app\models\forms
 */
class InstagramPostsSearch extends Model
{
    /** @var string $loginsStr Строка с логинами Instagram через запятую. */
    public $loginsStr;

    /** @var string[] $loginsList Массив логинов Instagram. */
    protected $loginsList;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['loginsStr'], 'string', 'max' => 150,'tooLong' => Yii::t('app', 'Максимум 150 символлов.')],
            [['loginsStr'], 'required', 'message' => Yii::t('app', 'Укажите хотя бы один аккаунт.')],
        ];
    }

    /**
     * Поиск постов Instagram.
     *
     * @param array $params
     * @return Media[]
     */
    public function search(array $params): array
    {
        if ($this->load($params) && $this->validate() && $this->sanitize()) {
            try {
                return (new InstagramPostsService())->getLastPosts($this->loginsList);
            } catch (Throwable $e) {
                Yii::error($e->getMessage());
            }
        }

        return [];
    }

    /**
     * Очистка данных.
     *
     * @return bool
     */
    protected function sanitize(): bool
    {
        /** Удаляем пробелы и лишние запятые. */
        $this->loginsStr = preg_replace(['~\s~', '~^,+~', '~,+$~'], '', $this->loginsStr);

        /** Удаляем лишние запятые. */
        $this->loginsStr = preg_replace('~,{2,}~', ',', $this->loginsStr);

        $this->loginsList = array_unique(array_filter(explode(',', $this->loginsStr)));

        /** Удаляем дубликаты. */
        $this->loginsStr = implode(',', $this->loginsList);

        return true;
    }
}
