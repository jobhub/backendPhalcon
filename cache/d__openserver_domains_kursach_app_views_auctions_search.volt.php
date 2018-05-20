<div class="page-header">
    <h1>
        Доступные тендеры
    </h1>
</div>

<?= $this->getContent() ?>
<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>№ Тендера</th>
            <th>Категория</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Стоимость</th>
            <th>Конец Тендера</th>

            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
            <?php foreach ($page->items as $auction) { ?>
                <tr>
                    <td><?= $auction->getAuctionid() ?></td>
                    <td><?= $auction->tasks->categories->getCategoryName() ?></td>
                    <td><?= $auction->tasks->getName() ?></td>
                    <td><?= $auction->tasks->getDescription() ?></td>
                    <td><?= $auction->tasks->getaddress() ?></td>
                    <td><?= $auction->tasks->getPrice() ?></td>
                    <td><?= $auction->getDateEnd() ?></td>

                    <td><?= $this->tag->linkTo(['auctions/viewing/' . $auction->getAuctionid(), 'Просмотреть']) ?></td>
                    <td><?= $this->tag->linkTo(['auctions/viewing/' . $auction->tasks->getUserId(), 'Профиль']) ?></td>
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
                <li><?= $this->tag->linkTo(['auctions/search', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>