
<?= $this->getContent() ?>

<div class="jumbotron">
    <h1>Страница не найдена</h1>
    <p>Извините, страница к котороый вы обращаетесть не существует или перемещена</p>
    <p><?= $this->tag->linkTo(['index', 'Home', 'class' => 'btn btn-primary']) ?></p>
</div>
