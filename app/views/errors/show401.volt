
{{ content() }}

<div class="jumbotron">
    <h1>Ошибка! Превышение полномочий!</h1>
    <p>У вас нет прав на совершение таких действий.</p>
    <p>{{ link_to('index', 'Home', 'class': 'btn btn-primary') }}</p>
</div>