<div class="page-header">
    <h1>
        Тендеры
    </h1>
    <p>
        <?= $this->tag->linkTo(['auctionsModer/new', 'Создать тендер']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['auctionsModer/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">ID тендера</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['auctionId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldAuctionid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">ID задания</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['taskId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldTaskid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldSelectedoffer" class="col-sm-2 control-label">Выбранное предложение (ID)</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['selectedOffer', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldSelectedoffer']) ?>
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
                <th>ID тендера</th>
            <th>ID задания</th>
            <th>Выбранное предложение (ID)</th>
            <th>Дата начала</th>
            <th>Дата завершения</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $auction) { ?>
            <tr>
                <td><?= $auction->getAuctionid() ?></td>
            <td><?= $auction->getTaskid() ?></td>
            <td><?= $auction->getSelectedoffer() ?></td>
            <td><?= $auction->getDatestart() ?></td>
            <td><?= $auction->getDateend() ?></td>

                <td><?= $this->tag->linkTo(['auctionsModer/edit/' . $auction->getAuctionid(), 'Изменение']) ?></td>
                <td><?= $this->tag->linkTo(['auctionsModer/delete/' . $auction->getAuctionid(), 'Удаление']) ?></td>
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
                <li><?= $this->tag->linkTo(['auctionsModer/index', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['auctionsModer/index?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['auctionsModer/index?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['auctionsModer/index?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>

