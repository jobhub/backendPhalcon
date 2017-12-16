<div class="page-header">
    <h1>
        Search offers
    </h1>
    <p>
        <?= $this->tag->linkTo(['offers/new', 'Create offers']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['offers/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldOfferid" class="col-sm-2 control-label">OfferId</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['offerId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldOfferid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">UserId</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['userId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Deadline</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['deadline', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldDeadline']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Description</label>
    <div class="col-sm-10">
        <?= $this->tag->textArea(['description', 'cols' => '30', 'rows' => '4', 'class' => 'form-control', 'id' => 'fieldDescription']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Price</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['price', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldPrice']) ?>
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
                <th>OfferId</th>
            <th>UserId</th>
            <th>Deadline</th>
            <th>Description</th>
            <th>Price</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $offer) { ?>
            <tr>
                <td><?= $offer->getOfferid() ?></td>
            <td><?= $offer->getUserid() ?></td>
            <td><?= $offer->getDeadline() ?></td>
            <td><?= $offer->getDescription() ?></td>
            <td><?= $offer->getPrice() ?></td>

                <td><?= $this->tag->linkTo(['offers/edit/' . $offer->getOfferid(), 'Edit']) ?></td>
                <td><?= $this->tag->linkTo(['offers/delete/' . $offer->getOfferid(), 'Delete']) ?></td>
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
                <li><?= $this->tag->linkTo(['offers/search', 'First']) ?></li>
                <li><?= $this->tag->linkTo(['offers/search?page=' . $page->before, 'Previous']) ?></li>
                <li><?= $this->tag->linkTo(['offers/search?page=' . $page->next, 'Next']) ?></li>
                <li><?= $this->tag->linkTo(['offers/search?page=' . $page->last, 'Last']) ?></li>
            </ul>
        </nav>
    </div>
</div>
