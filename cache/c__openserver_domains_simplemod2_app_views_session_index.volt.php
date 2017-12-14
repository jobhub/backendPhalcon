
<?= $this->getContent() ?>

<div class="page-header">
    <h2>Авторизация</h2>
</div>

<?= $this->tag->form(['session/start', 'id' => 'AuthorizedForm', 'onbeforesubmit' => 'return false']) ?>

    <fieldset>

         <?php foreach ($form as $element) { ?>
                    <div class="control-group">
                        <?= $element->label(['class' => 'control-label']) ?>

                        <div class="control">
                            <?= $element ?>
                        </div>
                    </div>
         <?php } ?>
         <div class="form-actions">
                     <?= $this->tag->submitButton(['Войти', 'class' => 'btn btn-primary', 'onclick' => 'return SignUp.validate();']) ?>
                 </div>

    </fieldset>
</form>
