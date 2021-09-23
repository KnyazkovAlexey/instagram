<?php

namespace app\services;

use SplMinHeap;

/**
 * Куча для хранения постов Instagram.
 * Обеспечивает экономию памяти при фильтрации и сортировке.
 * @link https://www.php.net/manual/ru/class.splminheap.php
 */
class InstagramPostsHeap extends SplMinHeap
{
    /** @var int Максимальное кол-во постов в куче */
    protected int $maxCount;

    public function __construct(int $maxCount)
    {
        $this->maxCount = $maxCount;
    }

    /**
     * @inheritDoc
     */
    public function compare($value1, $value2): int
    {
        return $value2->getCreatedTime() <=> $value1->getCreatedTime();
    }

    /**
     * @inheritDoc
     */
    public function insert($value)
    {
        //При достижении лимита, добавляем новый элемент лишь в том случае, если он больше наименьшего
        if (count($this) < $this->maxCount || ($value->getCreatedTime() >= $this->top()->getCreatedTime())) {
            parent::insert($value);
        }

        //Если лимит переполнен, выбрасываем из кучи наименьший элемент
        if ((count($this) > $this->maxCount)) {
            $this->extract();
        }
    }
}