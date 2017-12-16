<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous"><?= $this->tag->linkTo(['users', 'Назад']) ?></li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Добавление пользователя
    </h1>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['users/create', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

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
       <?= $this->tag->selectStatic(['role', ['' => '', 'User' => 'Пользователь', 'Guests' => 'Гость', 'Moderator' => 'Модератор'], 'class' => 'form-control', 'id' => 'fieldRole']) ?>
      </div>
</div>

<div class="form-group">
    <label for="fieldPhone" class="col-sm-2 control-label">Имя</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['firstname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldFirstname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldPassword" class="col-sm-2 control-label">Фамилия</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['lastname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldLastname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldRole" class="col-sm-2 control-label">Пол</label>
    <div class="col-sm-10">
       <?= $this->tag->selectStatic(['male', ['1' => 'Мужской', '0' => 'Женский'], 'class' => 'form-control', 'id' => 'fieldMale']) ?>
      </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Сохранить', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
