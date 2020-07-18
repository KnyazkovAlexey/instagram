<?php

namespace app\tests;

use Faker\Factory;
use Faker\Generator;

/**
 * Базовый класс для тестов.
 *
 * Class TestCase
 * @package app\tests
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Generator $faker */
    protected Generator $faker;

    /**
     * TestCase constructor.
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->faker = Factory::create();
    }
}