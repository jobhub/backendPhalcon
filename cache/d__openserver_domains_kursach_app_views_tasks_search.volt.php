<div class="page-header">
    <h1>
        Созданные задания
    </h1>
    <p> <?= $this->tag->linkTo(['tasks/new', 'Создать задание']) ?></p>
    <p> <?= $this->tag->linkTo(['tasks/mytasks/' . $userId, 'Мои задания']) ?></p>
    <p>  <?= $this->tag->linkTo(['offers/myoffers/' . $userId, 'Мои предложения']) ?></p>
    <p>  <?= $this->tag->linkTo(['tasks/doingtasks/' . $userId, 'Выполняемые задания']) ?></p>
</div>

<?= $this->getContent() ?>

<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Номер Задания</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Дата работ</th>
            <th>Стоимость</th>

            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
            <?php foreach ($page->items as $task) { ?>

                <tr>
                    <td><?= $task->getTaskid() ?></td>
                    <td><?= $task->categories->getCategoryName() ?></td>
                    <td><?= $task->getDescription() ?></td>
                    <td><?= $task->getaddress() ?></td>
                    <td><?= $task->getDeadline() ?></td>
                    <td><?= $task->getPrice() ?></td>

                    <td><?= $this->tag->linkTo(['tasks/edit/' . $task->getTaskid(), 'Редактировать']) ?></td>
                    <td><?= $this->tag->linkTo(['tasks/delete/' . $task->getTaskid(), 'Удалить']) ?></td>
                    <td><?= $this->tag->linkTo(['auctions/show/' . $task->getTaskid(), 'Тендер']) ?></td>
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
                <li><?= $this->tag->linkTo(['tasks/search', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/search?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/search?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/search?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>