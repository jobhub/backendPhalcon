<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['userinfo', 'Go Back']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Редактирование профиля
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['userinfo/save', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>



<div class="form-group">
    <label for="fieldFirstname" class="col-sm-2 control-label">Имя</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['firstname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldFirstname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPatronymic" class="col-sm-2 control-label">Отчество</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['patronymic', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldPatronymic']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldLastname" class="col-sm-2 control-label">Фамилия</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['lastname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldLastname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldBirthday" class="col-sm-2 control-label">Дата рождения</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['birthday', 'class' => 'form-control', 'id' => 'fieldBirthday']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldMale" class="col-sm-2 control-label">Пол</label>
    <div class="col-sm-10">
      <?= $this->tag->selectStatic(['male', ['1' => 'Мужской', '0' => 'Женский'], 'class' => 'form-control', 'id' => 'fieldMale']) ?>

    </div>
</div>

<div class="form-group">
    <label for="fieldAddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['address', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldAddress']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldAbout" class="col-sm-2 control-label">О себе</label>
    <div class="col-sm-10">
        <?= $this->tag->textArea(['about', 'cols' => '30', 'rows' => '4', 'class' => 'form-control', 'id' => 'fieldAbout']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldExecutor" class="col-sm-2 control-label">Исполнитель</label>
    <div class="col-sm-10">
      <?= $this->tag->selectStatic(['executor', ['1' => 'Да', '0' => 'Нет'], 'class' => 'form-control', 'id' => 'fieldExecutor']) ?>

    </div>
</div>


<?= $this->tag->hiddenField(['id']) ?>

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Сохранить', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
