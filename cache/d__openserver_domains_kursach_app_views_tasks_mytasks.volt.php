<div class="page-header">
    <h1>
        Созданные задания
    </h1>
    <p> <?= $this->tag->linkTo(['tasks/new', 'Создать задание']) ?></p>
    <p> <?= $this->tag->linkTo(['tasks/mytasks/' . $userId, 'Мои задания']) ?></p>
    <p>  <?= $this->tag->linkTo(['offers/myoffers/' . $userId, 'Мои предложения']) ?></p>
    <p>  <?= $this->tag->linkTo(['tasks/doingtasks/' . $userId, 'Мне выполняют задания']) ?></p>
    <p>  <?= $this->tag->linkTo(['tasks/workingtasks/' . $userId, 'Мои выполняемые задания']) ?></p>
</div>

<?= $this->getContent() ?>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Название</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Дата работ</th>
            <th>Стоимость</th>
            <th>Статус</th>

                <th colspan="2">Действия</th>
                <th>Тендер/Чат</th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $task) { ?>

            <tr>
                <td><?= $this->tag->linkTo(['auctions/show/' . $task->getTaskid(), $task->getName()]) ?></td>
            <td><?= $task->categories->getCategoryName() ?></td>
            <td><?= $task->getDescription() ?></td>
            <td><?= $task->getaddress() ?></td>
            <td><?= $task->getDeadline() ?></td>
            <td><?= $task->getPrice() ?></td>
            <td><?= $task->getStatus() ?></td>

                <td><?= $this->tag->linkTo(['tasks/editing/' . $task->getTaskid(), 'Редактировать']) ?></td>
                <td><?= $this->tag->linkTo(['tasks/delete/' . $task->getTaskid(), 'Удалить']) ?></td>
                <?php if ($task->status == 'Поиск') { ?>
                <td><?= $this->tag->linkTo(['auctions/show/' . $task->getTaskid(), 'Тендер']) ?></td>
                <?php } elseif ($task->status == 'Выполняется') { ?>
                <td><?= $this->tag->linkTo(['coordination/index/' . $task->getTaskid(), 'Чат']) ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php if ($page->total_pages > 1) { ?>
<div class="row">
    <div class="col-sm-1">
        <p class="pagination" style="line-height: 1.42857;padding: 6px 12px;">
            <?= $page->current . '/' . $page->total_pages ?>
        </p>
    </div>
    <div class="col-sm-11">
        <nav>
            <ul class="pagination">
                <li><?= $this->tag->linkTo(['tasks/mytasks/' . $userId, 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/mytasks/' . $userId . '?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/mytasks/' . $userId . '?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/mytasks/' . $userId . '?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>
<?php } ?>