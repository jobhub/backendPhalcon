<div class="page-header">
    <h1>
        Категории
    </h1>
    <p>
        {{ link_to("categories/new", "Создать категорию") }}
    </p>
</div>

{{ content() }}

{{ form("categories/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">ID категории</label>
    <div class="col-sm-10">
        {{ text_field("categoryId", "type" : "numeric", "class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryname" class="col-sm-2 control-label">Название категории</label>
    <div class="col-sm-10">
        {{ text_field("categoryName", "size" : 30, "class" : "form-control", "id" : "fieldCategoryname") }}
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
                <th>ID категории</th>
            <th>Название категории</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for categorie in page.items %}
            <tr>
                <td>{{ categorie.getCategoryid() }}</td>
            <td>{{ categorie.getCategoryname() }}</td>

                <td>{{ link_to("categories/edit/"~categorie.getCategoryid(), "Изменить") }}</td>
                <td>{{ link_to("categories/delete/"~categorie.getCategoryid(), "Удалить") }}</td>
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
                <li>{{ link_to("users/search", "Первая") }}</li>
                <li>{{ link_to("users/search?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("users/search?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("users/search?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
