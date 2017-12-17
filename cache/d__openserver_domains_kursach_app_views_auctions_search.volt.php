<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['auctions/index', 'Go Back']) ?></li>
            <li class="next"><?= $this->tag->linkTo(['auctions/new', 'Create ']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>Search result</h1>
</div>

<?= $this->getContent() ?>

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
