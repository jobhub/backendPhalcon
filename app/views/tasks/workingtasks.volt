<div class="page-header">
    <h1>
        Мне выполняют задания
    </h1>
    <p> {{ link_to("tasks/new", "Создать задание") }}</p>
    <p> {{ link_to("tasks/mytasks/"~userId, "Мои задания") }}</p>
    <p>  {{ link_to("offers/myoffers/"~userId, "Мои предложения") }}</p>
    <p>  {{ link_to("tasks/doingtasks/"~userId, "Мои выполняемые задания") }}</p>
    <p>  {{ link_to("tasks/workingtasks/"~userId, "Мне выполняют задания") }}</p>
</div>

{{ content() }}

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Номер Задания</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Дата работ</th>
            <th>Стоимость</th>
            <th>Статус</th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for task in page.items %}

            <tr>
                <td>{{ task.tasks.getTaskId() }}</td>
            <td>{{ task.tasks.categories.getCategoryName() }}</td>
            <td>{{ task.tasks.getDescription() }}</td>
            <td>{{ task.tasks.getaddress() }}</td>
            <td>{{ task.tasks.getDeadline() }}</td>
            <td>{{ task.tasks.getPrice() }}</td>
            <td>{{ task.tasks.getStatus() }}</td>

                <td>{{ link_to("tasks/edit/"~task.tasks.getTaskid(), "Редактировать") }}</td>
                <td>{{ link_to("tasks/delete/"~task.tasks.getTaskid(), "Удалить") }}</td>
                {% if task.tasks.status is 'Поиск'%}
                <td>{{ link_to("auctions/show/"~task.tasks.getTaskid(), "Тендер") }}</td>
                {% elseif task.tasks.status is 'Выполняется'%}
                <td>{{ link_to("coordination/index/"~task.tasks.getTaskid(), "Чат") }}</td>
                {% endif %}
            </tr>
        {% endfor %}
        {% endif %}
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-sm-1">
        <p class="pagination" style="line-height: 1.42857;padding: 6px 12px;">
            {{ page.current~"/"~page.total_pages }}
        </p>
    </div>
    <div class="col-sm-11">
        <nav>
            <ul class="pagination">
                <li>{{ link_to("tasks/workingtasks/"~userId, "Первая") }}</li>
                <li>{{ link_to("tasks/workingtasks/"~userId~"?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("tasks/workingtasks/"~userId~"?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("tasks/workingtasks/"~userId~"?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
