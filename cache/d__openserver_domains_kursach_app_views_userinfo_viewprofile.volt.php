<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['userinfo', 'Go Back']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Просмотр профиля
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['userinfo/save', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>



<div class="form-group">
    <label for="fieldFirstname" class="col-sm-2 control-label">Имя</label>
    <div class="col-sm-10">
        <?= $userinfo->getFirstname() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPatronymic" class="col-sm-2 control-label">Отчество</label>
    <div class="col-sm-10">
        <?= $userinfo->getPatronymic() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldLastname" class="col-sm-2 control-label">Фамилия</label>
    <div class="col-sm-10">
        <?= $userinfo->getLastname() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldBirthday" class="col-sm-2 control-label">Дата рождения</label>
    <div class="col-sm-10">
        <?= $userinfo->getBirthday() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldMale" class="col-sm-2 control-label">Пол</label>
    <div class="col-sm-10">
      <?= $userinfo->getMale() ?>

    </div>
</div>

<div class="form-group">
    <label for="fieldAddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        <?= $userinfo->getAddress() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldAbout" class="col-sm-2 control-label">О себе</label>
    <div class="col-sm-10">
        <?= $userinfo->getAbout() ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldExecutor" class="col-sm-2 control-label">Исполнитель</label>
    <div class="col-sm-10">
      <?= $userinfo->getExecutor() ?>

    </div>
</div>


</form>
