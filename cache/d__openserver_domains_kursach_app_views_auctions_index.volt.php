<div class="page-header">
    <h1>
        Доступные тендеры
    </h1>
</div>

<?= $this->getContent() ?>
<!--
<?= $this->tag->form(['auctions/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">Номер тендера</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['auctionId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldAuctionid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">DateStart</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['dateStart', 'class' => 'form-control', 'id' => 'fieldDatestart']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">DateEnd</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['dateEnd', 'class' => 'form-control', 'id' => 'fieldDateend']) ?>
    </div>
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Фильтр', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
-->
<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Название</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Стоимость</th>
            <th>Конец Тендера</th>
                <th>Заказчик</th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $auction) { ?>
            <tr>
                <td><?= $this->tag->linkTo(['auctions/viewing/' . $auction->auctions->getAuctionid(), $auction->auctions->tasks->getName()]) ?></td>
            <td><?= $auction->auctions->tasks->categories->getCategoryName() ?></td>
            <td><?= $auction->auctions->tasks->getDescription() ?></td>
            <td><?= $auction->auctions->tasks->getaddress() ?></td>
            <td><?= $auction->auctions->tasks->getPrice() ?></td>
            <td><?= $auction->auctions->getDateEnd() ?></td>

                <td><?= $this->tag->linkTo(['userinfo/viewprofile/' . $auction->auctions->tasks->getUserId(), $auction->auctions->tasks->users->userinfo->getLastname()]) ?></td>

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
                <li><?= $this->tag->linkTo(['auctions/index', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/index?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/index?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/indexs?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>
<?php } ?>

