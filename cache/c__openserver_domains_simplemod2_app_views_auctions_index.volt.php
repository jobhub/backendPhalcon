<div class="page-header">
    <h1>
        Search auctions
    </h1>
    <p>
        <?= $this->tag->linkTo(['auctions/new', 'Create auctions']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['auctions/search', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

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
        <?= $this->tag->submitButton(['Search', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
