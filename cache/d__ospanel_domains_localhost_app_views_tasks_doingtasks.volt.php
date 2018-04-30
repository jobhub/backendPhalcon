<div class="page-header">
    <h1>
        Мне выполняют задания
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
            <th>Цена</th>
            <th>Статус</th>
                <th colspan="2">Действия</th>
                 </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $task) { ?>

            <tr>
                <td><?= $this->tag->linkTo(['auctions/show/' . $task->tasks->getTaskid(), $task->tasks->getName()]) ?></td>
                            <td><?= $task->tasks->categories->getCategoryName() ?></td>
                            <td><?= $task->tasks->getDescription() ?></td>
                            <td><?= $task->tasks->getaddress() ?></td>
                            <td><?= $task->tasks->getDeadline() ?></td>
                            <td><?= $task->tasks->getPrice() ?></td>
                            <td><?= $task->tasks->getStatus() ?></td>

                <td><?= $this->tag->linkTo(['tasks/edit/' . $task->tasks->getTaskid(), 'Редактировать']) ?></td>
                <td><?= $this->tag->linkTo(['tasks/delete/' . $task->tasks->getTaskid(), 'Удалить']) ?></td>
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
                <li><?= $this->tag->linkTo(['tasks/doingtasks/' . $userId, 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/doingtasks/' . $userId . '?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/doingtasks/' . $userId . '?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['tasks/doingtasks/' . $userId . '?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>
<?php } ?>