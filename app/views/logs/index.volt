<div class="page-header">
    <h1>
        Логи
    </h1>
</div>

{{ content() }}

{{ form("logs/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID пользователя</label>
    <div class="col-sm-10">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldController" class="col-sm-2 control-label">Controller</label>
    <div class="col-sm-10">
        {{ text_field("controller", "size" : 30, "class" : "form-control", "id" : "fieldController") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldAction" class="col-sm-2 control-label">Action</label>
    <div class="col-sm-10">
        {{ text_field("action", "size" : 30, "class" : "form-control", "id" : "fieldAction") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDate" class="col-sm-2 control-label">Дата</label>
    <div class="col-sm-10">
        {{ date_field("date", "size" : 30, "class" : "form-control", "id" : "fieldDate") }}
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
                <th>LogId</th>
            <th>ID пользователя</th>
            <th>Controller</th>
            <th>Action</th>
            <th>Дата</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for log in page.items %}
            <tr>
                <td>{{ log.getLogid() }}</td>
            <td>{{ log.getUserid() }}</td>
            <td>{{ log.getController() }}</td>
            <td>{{ log.getAction() }}</td>
            <td>{{ log.getDate() }}</td>

                <td>{{ link_to("logs/delete/"~log.getLogid(), "Удалить") }}</td>
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
                <li>{{ link_to("logs/index", "Первая") }}</li>
                <li>{{ link_to("logs/index?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("logs/index?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("logs/index?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>