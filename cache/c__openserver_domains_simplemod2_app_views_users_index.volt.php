<div class="page-header">
    <h1>
        Пользователи
    </h1>
    <p>
        <?= $this->tag->linkTo(['users/new', 'Создать пользователя']) ?>
    </p>
</div>

<?= $this->getContent() ?>

<?= $this->tag->form(['users/index', 'method' => 'post', 'autocomplete' => 'off', 'class' => 'form-horizontal']) ?>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-1 control-label">ID</label>
    <div class="col-sm-2">
        <?= $this->tag->textField(['userId', 'type' => 'numeric', 'class' => 'form-control', 'id' => 'fieldUserid']) ?>
    </div>

    <label for="fieldEmail" class="col-sm-1 control-label">Email</label>
    <div class="col-sm-2">
        <?= $this->tag->textField(['email', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldEmail']) ?>
    </div>

    <label for="fieldPhone" class="col-sm-1 control-label">Телефон</label>
    <div class="col-sm-2">
        <?= $this->tag->textField(['phone', 'size' => 30, 'class' => 'form-control', 'id' => 'fieldPhone']) ?>
    </div>
</div>

<div class="form-group">
    <label for="fieldRole" class="col-sm-1 control-label">Роль</label>
    <div class="col-sm-2">
        <?= $this->tag->selectStatic(['role', ['' => '', 'User' => 'Пользователь', 'Guests' => 'Гость', 'Moderator' => 'Модератор'], 'class' => 'form-control', 'id' => 'fieldRole']) ?>
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
                <th>ID пользователя</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Роль</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($page->items)) { ?>
        <?php foreach ($page->items as $user) { ?>
            <tr>
                <td><?= $user->getUserid() ?></td>
            <td><?= $user->getEmail() ?></td>
            <td><?= $user->getPhone() ?></td>
            <td><?= $user->getRole() ?></td>

                <td><?= $this->tag->linkTo(['users/edit/' . $user->getUserid(), 'Edit']) ?></td>
                <td><?= $this->tag->linkTo(['users/delete/' . $user->getUserid(), 'Delete']) ?></td>
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
                <li><?= $this->tag->linkTo(['users/search', 'First']) ?></li>
                <li><?= $this->tag->linkTo(['users/search?page=' . $page->before, 'Previous']) ?></li>
                <li><?= $this->tag->linkTo(['users/search?page=' . $page->next, 'Next']) ?></li>
                <li><?= $this->tag->linkTo(['users/search?page=' . $page->last, 'Last']) ?></li>
            </ul>
        </nav>
    </div>
</div>


