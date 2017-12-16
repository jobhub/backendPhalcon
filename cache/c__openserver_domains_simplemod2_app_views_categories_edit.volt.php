<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['categories', 'Назад']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Изменение категории
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['categories/save', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldCategoryname" class="col-sm-2 control-label">Название категории</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['categoryName', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldCategoryname']) ?>
    </div>
</div>


<?= $this->tag->hiddenField(['id']) ?>

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Изменить', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
