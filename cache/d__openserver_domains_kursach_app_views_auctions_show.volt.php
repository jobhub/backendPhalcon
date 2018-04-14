<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['auctions', 'Go Back']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Тендер
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['offers/new', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        <?= $task->getName() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        <?= $task->categories->getCategoryName() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        <?= $task->getDescription() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldaddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        <?= $task->getAddress() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        <?= $task->getDeadline() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        <?= $task->getPrice() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">Дата начала тендера</label>
    <div class="col-sm-10">
        <?= $auction->getDateStart() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">Дата окончания тендера</label>
    <div class="col-sm-10">
        <?= $auction->getDateEnd() ?>
    </div>
</div>
</form>


<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Исполнитель</th>
            <th>Срок</th>
            <th>Описание</th>
            <th>Стоимость</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
            <?php foreach ($page->items as $offer) { ?>
                <tr>
<<<<<<< HEAD:cache/d__openserver_domains_kursach_app_views_auctions_show.volt.php
                    <td><?= $this->tag->linkTo(['userinfo/viewprofile/' . $offer->getUserId(), $offer->users->userinfo->getLastname()]) ?></td>
                    <td><?= $offer->getDeadline() ?></td>
                    <td><?= $offer->getDescription() ?></td>
                    <td><?= $offer->getPrice() ?></td>
                    <td><?= $this->tag->linkTo(['auctions/choice/' . $offer->getOfferId(), 'Выбрать']) ?></td>
=======
                    <td>{{ link_to("userinfo/viewprofile/"~offer.getUserId(), offer.users.userinfo.getLastname()) }}</td>
                    <td>{{ offer.getDeadline() }}</td>
                    <td>{{ offer.getDescription() }}</td>
                    <td>{{ offer.getPrice() }}</td>
                    <td>{{ link_to("auctions/choice/"~offer.getOfferId(), "Выбрать") }}</td>
>>>>>>> 29ec5cd9a151b8ac0d7ecf95413ffc7a172e2843:app/views/auctions/show.volt
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
                <li><?= $this->tag->linkTo(['auctions/search', 'Первая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->before, 'Предыдущая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->next, 'Следующая']) ?></li>
                <li><?= $this->tag->linkTo(['auctions/search?page=' . $page->last, 'Последняя']) ?></li>
            </ul>
        </nav>
    </div>
</div>
<?php } ?>