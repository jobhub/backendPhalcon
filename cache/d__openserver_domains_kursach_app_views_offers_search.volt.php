<div class="page-header">
    <h1>
        Ваши предложения
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
                <th>Номер предложения</th>
                <th>Наименование работ</th>
                <th>Описание работ</th>
            <th>Тендер</th>
            <th>Описание предложения</th>
            <th>Сроки</th>
            <th>Стоимость</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $offers) { ?>

            <tr>
                <td><?= $offers->getOfferId() ?></td>
                <td><?= $offers->auctions->tasks->getName() ?></td>
                <td><?= $offers->auctions->tasks->getDescription() ?></td>
            <td><?= $this->tag->linkTo(['auctions/viewing/' . $offers->getAuctionId(), 'Тендер']) ?></td>
            <td><?= $offers->getDescription() ?></td>
            <td><?= $offers->getDeadline() ?></td>
            <td><?= $offers->getPrice() ?></td>

                <td><?= $this->tag->linkTo(['offers/editing/' . $offers->getOfferId(), 'Редактировать']) ?></td>
                <td><?= $this->tag->linkTo(['offers/deleting/' . $offers->getOfferId(), 'Удалить']) ?></td>
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
                <li><?= $this->tag->linkTo(['offers/search', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['offers/search?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['offers/search?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['offers/search?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>