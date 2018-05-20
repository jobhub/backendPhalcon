<div class="page-header">
    <h1>
       Сообщения
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['messages/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldMessageid" class="col-sm-2 control-label">ID сообщения</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['messageId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldMessageid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">ID аукциона</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['auctionId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldAuctionid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldInput" class="col-sm-2 control-label">Тип сообщения</label>
    <div class="col-sm-10">
        <?= $this->tag->selectStatic(['input', ['' => '', '1' => 'От исполнителя', '0' => 'От заказчика'], 'class' => 'form-control', 'id' => 'fieldInput']) ?>
    </div>
</div>

<!--<div class="form-group">
    <label for="fieldDate" class="col-sm-2 control-label">Дата и время отправки</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['date', 'type' => 'datetime', 'class' => 'form-control', 'id' => 'fieldDate']) ?>
    </div>
</div>-->


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
                <th>ID сообщения</th>
            <th>ID аукциона</th>
            <th>Тип сообщения</th>
            <th>Текст сообщения</th>
            <th>Дата и время отправки</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $message) { ?>
            <tr>
                <td><?= $message->getMessageid() ?></td>
            <td><?= $message->getAuctionid() ?></td>
            <td>
            <?php if ($message->getInput() == 1) { ?>
                         От исполнителя
                        <?php } else { ?>
                         От заказчика
                        <?php } ?>
            </td>
            <td><?= $message->getMessage() ?></td>
            <td><?= $message->getDate() ?></td>

                <td><?= $this->tag->linkTo(['messages/edit/' . $message->getMessageid(), 'Изменить']) ?></td>
                <td><?= $this->tag->linkTo(['messages/delete/' . $message->getMessageid(), 'Удалить']) ?></td>
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
                <li><?= $this->tag->linkTo(['messages/index', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['messages/index?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['messages/index?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['messages/index?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>