<div class="page-header">
    <h1>
        Search auctions
    </h1>
    <p>
        <?= $this->tag->linkTo(['auctions/new', 'Create auctions']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['auctions/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">AuctionId</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['auctionId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldAuctionid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">TaskId</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['taskId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldTaskid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldSelectedoffer" class="col-sm-2 control-label">SelectedOffer</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['selectedOffer', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldSelectedoffer']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">DateStart</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['dateStart', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldDatestart']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">DateEnd</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['dateEnd', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldDateend']) ?>
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
                <th>AuctionId</th>
            <th>TaskId</th>
            <th>SelectedOffer</th>
            <th>DateStart</th>
            <th>DateEnd</th>

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

                <td><?= $this->tag->linkTo(['auctions/edit/' . $auction->getAuctionid(), 'Edit']) ?></td>
                <td><?= $this->tag->linkTo(['auctions/delete/' . $auction->getAuctionid(), 'Delete']) ?></td>
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
                <li><?= $this->tag->linkTo(['auctions/search', 'First']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->before, 'Previous']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->next, 'Next']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->last, 'Last']) ?></li>
            </ul>
        </nav>
    </div>
</div>

