<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <?= $this->tag->getTitle() ?>
        <?= $this->tag->stylesheetLink('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css') ?>
        <?= $this->tag->stylesheetLink('css/bootstrap-theme.min.css') ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Your invoices">
        <meta name="author" content="Phalcon Team">
    </head>
    <body>
        <?= $this->getContent() ?>
        <?= $this->tag->javascriptInclude('js/jquery.min.js') ?>
        <?= $this->tag->javascriptInclude('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js') ?>
        <?= $this->tag->javascriptInclude('js/utils.js') ?>
    </body>
</html>
