<div class="page-header">
    <h1>
        Задания
    </h1>
    <p>
        {{ link_to("tasksModer/new", "Создать задание") }}
    </p>
</div>

{{ content() }}

{{ form("tasksModer/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">ID задания</label>
    <div class="col-sm-10">
        {{ text_field("taskId", "type" : "numeric", "class" : "form-control", "id" : "fieldTaskid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID пользователя</label>
    <div class="col-sm-10">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ select('categoryId', categories, 'using':['categoryId', 'categoryName'],"useEmpty":true,"emptyValue":null,
        'emptyText':'',"class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ text_area("description", "size" : 30, "class" : "form-control", "id" : "fieldDescription") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Время завершения выполнения</label>
    <div class="col-sm-10">
        {{ date_field("deadline", "size" : 30, "class" : "form-control", "id" : "fieldDeadline") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Цена</label>
    <div class="col-sm-10">
        {{ text_field("price", "type" : "numeric", "class" : "form-control", "id" : "fieldPrice") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Фильтр', 'class': 'btn btn-default') }}
    </div>
</div>

</form>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID задания</th>
            <th>ID пользователя</th>
            <th>Категория</th>
            <th>Описание</th>
            <th>Дата завершения</th>
            <th>Цена</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for task in page.items %}
            <tr>
                <td>{{ task.getTaskid() }}</td>
            <td>{{ task.getUserid() }}</td>
            <td>{{ task.categories.getCategoryName() }}</td>
            <td>{{ task.getDescription() }}</td>
            <td>{{ task.getDeadline() }}</td>
            <td>{{ task.getPrice() }}</td>

                <td>{{ link_to("tasks/edit/"~task.getTaskid(), "Изменить") }}</td>
                <td>{{ link_to("tasks/delete/"~task.getTaskid(), "Удалить") }}</td>
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
                <li>{{ link_to("tasks/index", "Первая") }}</li>
                <li>{{ link_to("tasks/index?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("tasks/index?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("tasks/index?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
