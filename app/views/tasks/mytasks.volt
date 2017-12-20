<div class="page-header">
    <h1>
        Созданные задания
    </h1>
    <p> {{ link_to("tasks/new", "Создать задание") }}</p>
    <p> {{ link_to("tasks/mytasks/"~userId, "Мои задания") }}</p>
    <p>  {{ link_to("offers/myoffers/"~userId, "Мои предложения") }}</p>
    <p>  {{ link_to("tasks/doingtasks/"~userId, "Выполняемые задания") }}</p>
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
                <td>{{ task.getTaskId() }}</td>
            <td>{{ task.categories.getCategoryName() }}</td>
            <td>{{ task.getDescription() }}</td>
            <td>{{ task.getaddress() }}</td>
            <td>{{ task.getDeadline() }}</td>
            <td>{{ task.getPrice() }}</td>

                <td>{{ link_to("tasks/edit/"~task.getTaskid(), "Редактировать") }}</td>
                <td>{{ link_to("tasks/delete/"~task.getTaskid(), "Удалить") }}</td>
                {% if task.status is 'Поиск'%}
                <td>{{ link_to("auctions/show/"~task.getTaskid(), "Тендер") }}</td>
                {% elseif task.status is 'Выполняется'%}
                <td>{{ link_to("coordination/index/"~task.getTaskid(), "Чат") }}</td>
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
                <li>{{ link_to("tasks/search", "Первая") }}</li>
                <li>{{ link_to("tasks/search?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("tasks/search?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("tasks/search?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
