<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['offers', 'Назад']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Изменение предложения
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['offers/saving', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата завершения выполнения</label>
    <div class="col-sm-10">
    <?= $this->tag->dateField(['deadline', 'form-control', 'id' => 'fieldDeadline']) ?>
</div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        <?= $this->tag->textArea(['description', 'cols' => '30', 'rows' => '4', 'class' => 'form-control', 'id' => 'fieldDescription']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Цена</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['price', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldPrice']) ?>
    </div>
</div>

<?= $this->tag->hiddenField(['id']) ?>

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Изменить', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>