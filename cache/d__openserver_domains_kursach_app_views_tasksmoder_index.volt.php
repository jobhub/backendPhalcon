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
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['name', 'size' => 50, 'class' => 'form-control', 'id' => 'fieldName']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">Пользователь</label>
    <div class="col-sm-10">
        <!--<?= $this->tag->textField(['userId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>-->
        <?= $this->tag->select(['userId', $users, 'using' => ['userId', 'email'], 'useEmpty' => true, 'emptyValue' => null, 'emptyText' => '', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        <?= $this->tag->select(['categoryId', $categories, 'using' => ['categoryId', 'categoryName'], 'useEmpty' => true, 'emptyValue' => null, 'emptyText' => '', 'class' => 'form-control', 'id' => 'fieldCategoryid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldStatus" class="col-sm-2 control-label">Статус</label>
    <div class="col-sm-10">
        <?= $this->tag->selectStatic(['status', ['Поиск' => 'Поиск', 'Выполняется' => 'Выполняется', 'Завершено' => 'Завершено'], 'useEmpty' => true, 'emptyValue' => null, 'emptyText' => '', 'class' => 'form-control', 'id' => 'fieldStatus']) ?>
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
            <th>Название</th>
            <th>Пользователь</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Дата завершения</th>
            <th>Статус</th>
            <th>Цена</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $task) { ?>
            <tr>
                <td><?= $task->getName() ?></td>
            <td><?= $this->tag->linkTo(['userinfo/viewprofile/' . $task->users->getUserId(), $task->users->getEmail()]) ?></td>
            <td><?= $task->categories->getCategoryName() ?></td>
            <td><?= $task->getDescription() ?></td>
            <td><?= $task->getAddress() ?></td>
            <td><?= $task->getDeadline() ?></td>
            <td><?= $task->getStatus() ?></td>
            <td><?= $task->getPrice() ?></td>

                <td><?= $this->tag->linkTo(['tasksModer/edit/' . $task->getTaskid(), 'Изменить']) ?></td>
                <td><?= $this->tag->linkTo(['tasksModer/delete/' . $task->getTaskid(), 'Удалить']) ?></td>
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
                <li><?= $this->tag->linkTo(['tasksModer/index', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['tasksModer/index?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['tasksModer/index?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/index?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>
