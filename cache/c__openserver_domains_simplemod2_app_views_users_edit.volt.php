<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['users', 'Go Back']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Изменение данных пользователя
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['users/save', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<h2>Основные данные</h2>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['userId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldEmail" class="col-sm-2 control-label">Email</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['email', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldEmail']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPhone" class="col-sm-2 control-label">Телефон</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['phone', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldPhone']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Пароль</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['password', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldPassword']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldRole" class="col-sm-2 control-label">Роль</label>
    <div class="col-sm-10">
        <?= $this->tag->selectStatic(['role', ['User' => 'Пользователь', 'Guests' => 'Гость', 'Moderator' => 'Модератор'], 'class' => 'form-control', 'id' => 'fieldRole']) ?>
    </div>
</div>

<h2>Персональные данные</h2>

<div class="form-group">
    <label for="fieldPhone" class="col-sm-2 control-label">Имя</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['firstname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldFirstname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Отчество</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['patronymic', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldPatronymic']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Фамилия</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['lastname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldLastname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Дата рождения</label>
    <div class="col-sm-10">
        <?= $this->tag->dateField(['birthday', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldBirthday']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldRole" class="col-sm-2 control-label">Пол</label>
    <div class="col-sm-10">
       <?= $this->tag->selectStatic(['male', ['1' => 'Мужской', '0' => 'Женский'], 'class' => 'form-control', 'id' => 'fieldMale']) ?>
      </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['address', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldAdress']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">О пользователе</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['about', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldAbout']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldRole" class="col-sm-2 control-label">Исполнитель</label>
    <div class="col-sm-10">
       <?= $this->tag->selectStatic(['executor', ['1' => 'Исполнитель', '0' => 'Заказчик'], 'class' => 'form-control', 'id' => 'fieldExecutor']) ?>
      </div>
</div>

<h2>Настроки</h2>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Радиус</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['radius', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldRadius']) ?>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Изменить', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
