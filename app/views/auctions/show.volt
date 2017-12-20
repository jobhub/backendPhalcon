<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctions", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Тендер
    </h1>
</div>

{{ content() }}

{{ form("offers/new", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        {{ task.getName() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ task.categories.getCategoryName() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ task.getDescription() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldaddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        {{ task.getAddress() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        {{ task.getDeadline() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        {{ task.getPrice() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">Дата начала тендера</label>
    <div class="col-sm-10">
        {{ auction.getDateStart()}}
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">Дата окончания тендера</label>
    <div class="col-sm-10">
        {{ auction.getDateEnd() }}
    </div>
</div>
</form>


<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Номер предложения</th>
            <th>Пользователь</th>
            <th>Срок</th>
            <th>Описание</th>
            <th>Стоимость</th>

            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
            {% for offer in page.items %}
                <tr>
                    <td>{{ offer.getOfferId() }}</td>
                    <td>{{ link_to("userinfo/viewprofile/"~offer.getUserId(), "Профиль") }}</td>
                    <td>{{ offer.getDeadline() }}</td>
                    <td>{{ offer.getDescription() }}</td>
                    <td>{{ offer.getPrice() }}</td>
                    <td>{{ link_to("auctions/choice/"~offer.getOfferId(), "Выбрать") }}</td>
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
                <li>{{ link_to("auctions/search", "Первая") }}</li>
                <li>{{ link_to("auctions/search?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("auctions/search?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("auctions/search?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>
{% endif %}