{{ elements.getTabs() }}
<div class="page-header">
    <h1>
        Предложения
    </h1>
    <!--<p>
        {{ link_to("offers/new", "Создать предложение") }}
    </p>-->
</div>

{{ content() }}

{{ form("offers/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">Пользователь</label>
    <div class="col-sm-10">
        {{ select('userId', users, 'using':['userId', 'email'],'useEmpty':true,
                'emptyValue':null, 'emptyText':'', 'class':'form-control', 'id':'fieldUserid') }}
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
            <td>{{ link_to("userinfo/viewprofile/"~offer.users.getUserId(),offer.users.getEmail()) }}</td>
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

{% if page.total_pages>1 %}
<div class="row">
    <div class="col-sm-1">
        <p class="pagination" style="line-height: 1.42857;padding: 6px 12px;">
            {{ page.current~"/"~page.total_pages }}
        </p>
    </div>
    <div class="col-sm-11">
        <nav>
            <ul class="pagination">
                <li>{{ link_to("offers/index", "Первая") }}</li>
                <li>{{ link_to("offers/index?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("offers/index?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("offers/index?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
{% endif %}