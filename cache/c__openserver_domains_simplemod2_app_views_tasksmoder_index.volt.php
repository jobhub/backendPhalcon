<div class="page-header">
    <h1>
        Задания
    </h1>
    <p>
        <?= $this->tag->linkTo(['tasksModer/new', 'Создать задание']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['tasksModer/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">ID задания</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['taskId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldTaskid']) ?>
    </div>
</div>

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
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Фильтр', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID задания</th>
            <th>ID пользователя</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Дата завершения</th>
            <th>Цена</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $task) { ?>
            <tr>
                <td><?= $task->getTaskid() ?></td>
            <td><?= $task->getUserid() ?></td>
            <td><?= $task->categories->getCategoryName() ?></td>
            <td><?= $task->getDescription() ?></td>
            <td><?= $task->getDeadline() ?></td>
            <td><?= $task->getPrice() ?></td>

                <td><?= $this->tag->linkTo(['tasks/edit/' . $task->getTaskid(), 'Изменить']) ?></td>
                <td><?= $this->tag->linkTo(['tasks/delete/' . $task->getTaskid(), 'Удалить']) ?></td>
            </tr>
        <?php } ?>
        <?php } ?>
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-sm-1">
        <p class="pagination" style="line-height: 1.42857;padding: 6px 12px;">
            <?= $page->current . '/' . $page->total_pages ?>
        </p>
    </div>
    <div class="col-sm-11">
        <nav>
            <ul class="pagination">
                <li><?= $this->tag->linkTo(['users/search', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['users/search?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['users/search?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['users/search?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>
