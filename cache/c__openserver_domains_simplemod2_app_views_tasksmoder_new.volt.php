<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['tasks', 'Назад']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Создание задания
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['tasksModer/create', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID пользователя</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['userId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        <?= $this->tag->select(['categoryId', $categories, 'using' => ['categoryId', 'categoryName'], 'useEmpty' => true, 'emptyValue' => null, 'emptyText' => '', 'class' => 'form-control', 'id' => 'fieldCategoryid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        <?= $this->tag->textArea(['description', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldDescription']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Время завершения выполнения</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['deadline', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldDeadline']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Цена</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['price', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldPrice']) ?>
    </div>



<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Создать', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
