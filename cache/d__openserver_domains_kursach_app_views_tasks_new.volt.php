<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['tasks', 'Go Back']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Создание задания
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['tasks/create', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['name', 'size' => 50, 'class' => 'form-control', 'id' => 'fieldName']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        <?= $this->tag->select(['categoryId', $categories, 'using' => ['categoryId', 'categoryName'], 'class' => 'form-control', 'id' => 'fieldCategoryid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['description', 'size' => 1000, 'class' => 'form-control', 'id' => 'fieldDescription']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldaddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['address', 'size' => 100, 'class' => 'form-control', 'id' => 'fieldaddress']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['deadline', 'class' => 'form-control', 'id' => 'fieldDeadline']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['price', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldPrice']) ?>
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Создать', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
