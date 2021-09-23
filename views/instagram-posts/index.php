<?php

use yii\web\View;
use InstagramScraper\Model\Media;
use yii\widgets\ActiveForm;
use app\models\search\InstagramPostsSearch;
use yii\widgets\Pjax;
use yii\helpers\Html;

/**
 * @var Media[] $posts
 * @var InstagramPostsSearch $search
 * @var $this View
 */

$this->title = Yii::t('app', 'Посты Instagram');
?>

<h1><?= $this->title ?></h1><br>

<?php Pjax::begin(['id' => 'pjaxPosts', 'timeout' => false]); ?>

<?php $form = ActiveForm::begin([
    'id' => 'search-form',
    'method' => 'get',
    'action' => Yii::$app->request->pathInfo,
    'options' => [
        'data-pjax' => true,
    ],
]) ?>

<?= $form->field($search, 'loginsStr')->textInput(['class' => 'form-control',
    'placeholder' => Yii::t('app', 'Введите аккаунты через запятую')])->label(false) ?>

<button id="search-button" class="btn btn-info"><?= Yii::t('app', 'Искать') ?></button>

<?php ActiveForm::end() ?>

<br>

<?php if (empty($posts)): ?>
    <p id="empty-table-note"><?= Yii::t('app', 'Ничего не найдено.') ?></p>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
    <?= date('d.m.Y H:i', $post->getCreatedTime()) ?>

    <a href="https://www.instagram.com/<?= Html::encode($post->getOwner()->getUsername()) ?>" target="_blank">
        <?= Html::encode($post->getOwner()->getUsername()) ?>
    </a><br>

    <a href="<?= $post->getLink() ?>" target="_blank">
        <img src="<?= $post->getImageHighResolutionUrl() ?>" style="width:256px;">
    </a>

    <p>
        <?= Html::encode($post->getCaption()) ?>
    </p><br>
<?php endforeach; ?>

<?php Pjax::end(); ?>

<script>

</script>

<?php
$this->registerJs('
    /** Обновление постов каждые 10 минут */
    setInterval(function() {
        $.pjax.reload({container: "#pjaxPosts"});
    }, 10 * 60 * 1000);

    /** Даём обратную связь, что надо подождать, пока список обновится */
    $("body").on("submit", "#search-form", function() {
        $("#search-button").prop("disabled", true).text("Ждите...");
        $("#empty-table-note").hide();
    });
', View::POS_READY);
?>
