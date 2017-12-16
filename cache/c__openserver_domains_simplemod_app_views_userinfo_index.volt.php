<div class="page-header">
    <h1>
        Search user_info
    </h1>
    <p>
        <?= $this->tag->linkTo(['user_info/new', 'Create user_info']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['user_info/search', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldFirstname" class="col-sm-2 control-label">Firstname</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['firstname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldFirstname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldLastname" class="col-sm-2 control-label">Lastname</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['lastname', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldLastname']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldBirthday" class="col-sm-2 control-label">Birthday</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['birthday', 'type' => 'date', 'class' => 'form-control', 'id' => 'fieldBirthday']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldMale" class="col-sm-2 control-label">Male</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['male', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldMale']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldAddress" class="col-sm-2 control-label">Address</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['address', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldAddress']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldAbout" class="col-sm-2 control-label">About</label>
    <div class="col-sm-10">
        <?= $this->tag->textArea(['about', 'cols' => '30', 'rows' => '4', 'class' => 'form-control', 'id' => 'fieldAbout']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldExecutor" class="col-sm-2 control-label">Executor</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['executor', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldExecutor']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldUserId" class="col-sm-2 control-label">User</label>
    <div class="col-sm-10">
        <?= $this->tag->textField(['user_id', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserId']) ?>
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <?= $this->tag->submitButton(['Search', 'class' => 'btn btn-default']) ?>
    </div>
</div>

</form>
