<div class="page-header">
    <h1>
        Доступные тендеры
    </h1>
</div>

{{ content() }}
<!--
{{ form("auctions/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">Номер тендера</label>
    <div class="col-sm-10">
        {{ text_field("auctionId", "type" : "numeric", "class" : "form-control", "id" : "fieldAuctionid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">DateStart</label>
    <div class="col-sm-10">
        {{ date_field("dateStart","class":"form-control","id" : "fieldDatestart") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">DateEnd</label>
    <div class="col-sm-10">
        {{ date_field("dateEnd","class":"form-control","id" : "fieldDateend") }}
    </div>
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
                <th>№ Тендера</th>
            <th>Категория</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Адрес</th>
            <th>Стоимость</th>
            <th>Конец Тендера</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for auction in page.items %}
            <tr>
                <td>{{ auction.getAuctionid() }}</td>
            <td>{{ auction.tasks.categories.getCategoryName() }}</td>
            <td>{{ auction.tasks.getName() }}</td>
            <td>{{ auction.tasks.getDescription() }}</td>
            <td>{{ auction.tasks.getaddress() }}</td>
            <td>{{ auction.tasks.getPrice() }}</td>
            <td>{{ auction.getDateEnd() }}</td>

                <td>{{ link_to("auctions/viewing/"~auction.getAuctionid(), "Просмотреть") }}</td>
                <td>{{ link_to("auctions/viewing/"~auction.tasks.getUserId(), "Профиль") }}</td>
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
                <li>{{ link_to("auctions/search", "Первая") }}</li>
                <li>{{ link_to("auctions/search?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("auctions/search?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("auctions/search?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>

