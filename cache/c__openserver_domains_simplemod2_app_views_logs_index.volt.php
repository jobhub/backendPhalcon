<div class="page-header">
    <h1>
        Search logs
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['logs/search', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldLogid" class="col-sm-2 control-label">LogId</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['logId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldLogid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">UserId</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['userId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldController" class="col-sm-2 control-label">Controller</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['controller', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldController']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldAction" class="col-sm-2 control-label">Action</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['action', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldAction']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDate" class="col-sm-2 control-label">Date</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['date', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldDate']) ?>
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Search', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>LogId</th>
            <th>UserId</th>
            <th>Controller</th>
            <th>Action</th>
            <th>Date</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $log) { ?>
            <tr>
                <td><?= $log->getLogid() ?></td>
            <td><?= $log->getUserid() ?></td>
            <td><?= $log->getController() ?></td>
            <td><?= $log->getAction() ?></td>
            <td><?= $log->getDate() ?></td>

                <td><?= $this->tag->linkTo(['logs/edit/' . $log->getLogid(), 'Edit']) ?></td>
                <td><?= $this->tag->linkTo(['logs/delete/' . $log->getLogid(), 'Delete']) ?></td>
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
                <li><?= $this->tag->linkTo(['logs/search', 'First']) ?></li>
                <li><?= $this->tag->linkTo(['logs/search?page=' . $page->before, 'Previous']) ?></li>
                <li><?= $this->tag->linkTo(['logs/search?page=' . $page->next, 'Next']) ?></li>
                <li><?= $this->tag->linkTo(['logs/search?page=' . $page->last, 'Last']) ?></li>
            </ul>
        </nav>
    </div>
</div>