<?php

namespace app\models\search;

use app\services\InstagramPostsService;
use InstagramScraper\Model\Media;
use yii\base\Model;
use Yii;

/**
 * Модель для поиска постов Instagram.
 *
 * Class InstagramPostsSearch
 * @package app\models\search
 *
 * @property string[] $logins Массив логинов Instagram.
 */
class InstagramPostsSearch extends Model
{
    /** @var string|null $loginsStr Строка с логинами Instagram через запятую. */
    public ?string $loginsStr = null;

    /** @var string[] $logins Массив логинов Instagram. */
    protected array $logins = [];

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['loginsStr'], 'string', 'max' => 150, 'tooLong' => Yii::t('app', 'Максимум 150 символов.')],
            [['loginsStr'], 'required', 'message' => Yii::t('app', 'Укажите хотя бы один аккаунт.')],
            [['loginsStr'], 'match', 'pattern' => '~[A-Za-z]~',
                'message' => Yii::t('app', 'Укажите хотя бы один аккаунт.')],
            [['loginsStr'], 'match', 'pattern' => '~^[A-Za-z0-9,_\.\s]+$~',
                'message' => Yii::t('app', 'Недопустимые символы в списке аккаунтов.')],
        ];
    }

    /**
     * @inheritDoc
     */
    public function formName(): string
    {
        return 'search';
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
            return (new InstagramPostsService())->getLastPosts($this->logins);
        }

        return [];
    }

    /**
     * Очистка данных.
     *
     * @return bool
     */
    public function sanitize(): bool
    {
        /** Удаляем пробелы и лишние запятые. */
        $this->loginsStr = preg_replace(['~\s~', '~^,+~', '~,+$~'], '', $this->loginsStr);

        /** Удаляем лишние запятые. */
        $this->loginsStr = preg_replace('~,{2,}~', ',', $this->loginsStr);

        $this->logins = array_unique(array_filter(explode(',', $this->loginsStr)));

        /** Удаляем дубликаты. */
        $this->loginsStr = implode(',', $this->logins);

        return true;
    }

    /**
     * Массив логинов Instagram.
     *
     * @return string[]
     */
    public function getLogins(): array
    {
        return $this->logins;
    }
}
