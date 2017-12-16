<div class="page-header">
    <h1>
        Предложения
    </h1>
    <p>
        {{ link_to("offers/new", "Создать предложение") }}
    </p>
</div>

{{ content() }}

{{ form("offers/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldOfferid" class="col-sm-2 control-label">ID предложения</label>
    <div class="col-sm-10">
        {{ text_field("offerId", "type" : "numeric", "class" : "form-control", "id" : "fieldOfferid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID пользователя</label>
    <div class="col-sm-10">
        <!--{{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}-->
        {{ select('userId', users, 'using':['userid', 'firstname'],"class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>



<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата завершения выполнения</label>
    <div class="col-sm-10">
        {{ date_field("deadline", "size" : 30, "class" : "form-control", "id" : "fieldDeadline") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ text_area("description", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldDescription") }}
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
                <th>ID предложения</th>
            <th>ID пользователя</th>
            <th>Дата завершения выполнения</th>
            <th>Описание</th>
            <th>Цена</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for offer in page.items %}
            <tr>
                <td>{{ offer.getOfferid() }}</td>
            <td>{{ offer.getUserid() }}</td>
            <td>{{ offer.getDeadline() }}</td>
            <td>{{ offer.getDescription() }}</td>
            <td>{{ offer.getPrice() }}</td>

                <td>{{ link_to("offers/edit/"~offer.getOfferid(), "Изменить") }}</td>
                <td>{{ link_to("offers/delete/"~offer.getOfferid(), "Удалить") }}</td>
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
