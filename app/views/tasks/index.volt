<div class="page-header">
    <h1>
        Созданные задания
    </h1>
    <p>
        {{ link_to("tasks/new", "Создать задание") }}
    </p>
</div>

{{ content() }}
<!--
{{ form("tasks/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">Номер задания</label>
    <div class="col-sm-10">
        {{ text_field("taskId", "type" : "numeric", "class" : "form-control", "id" : "fieldTaskid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ select('categoryId',categories,"using":["categoryId","categoryName"],'useEmpty': true, 'emptyText': '', 'emptyValue': '',"class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>


<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        {{ date_field("deadline","class":"form-control","id" : "fieldDeadline") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
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
-->
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
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for task in page.items %}

            <tr>
                <td>{{ task.getTaskid() }}</td>
            <td>{{ task.getCategoryid() }}</td>
            <td>{{ task.getDescription() }}</td>
            <td>{{ task.getaddress() }}</td>
            <td>{{ task.getDeadline() }}</td>
            <td>{{ task.getPrice() }}</td>

                <td>{{ link_to("tasks/edit/"~task.getTaskid(), "Редактировать") }}</td>
                <td>{{ link_to("tasks/delete/"~task.getTaskid(), "Удалить") }}</td>
                <td>{{ link_to("auctions/show/"~task.getTaskid(), "Аукцион") }}</td>
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
                <li>{{ link_to("tasks/search", "First") }}</li>
                <li>{{ link_to("tasks/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("tasks/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("tasks/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
