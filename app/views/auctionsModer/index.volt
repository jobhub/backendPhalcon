<div class="page-header">
    <h1>
        Тендеры
    </h1>
    <p>
        {{ link_to("auctionsModer/new", "Создать тендер") }}
    </p>
</div>

{{ content() }}

{{ form("auctionsModer/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">ID тендера</label>
    <div class="col-sm-10">
        {{ text_field("auctionId", "type" : "numeric", "class" : "form-control", "id" : "fieldAuctionid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">ID задания</label>
    <div class="col-sm-10">
        {{ text_field("taskId", "type" : "numeric", "class" : "form-control", "id" : "fieldTaskid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldSelectedoffer" class="col-sm-2 control-label">Выбранное предложение (ID)</label>
    <div class="col-sm-10">
        {{ text_field("selectedOffer", "type" : "numeric", "class" : "form-control", "id" : "fieldSelectedoffer") }}
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
                <th>ID тендера</th>
            <th>ID задания</th>
            <th>Выбранное предложение (ID)</th>
            <th>Дата начала</th>
            <th>Дата завершения</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for auction in page.items %}
            <tr>
                <td>{{ auction.getAuctionid() }}</td>
            <td>{{ auction.getTaskid() }}</td>
            <td>{{ auction.getSelectedoffer() }}</td>
            <td>{{ auction.getDatestart() }}</td>
            <td>{{ auction.getDateend() }}</td>

                <td>{{ link_to("auctionsModer/edit/"~auction.getAuctionid(), "Изменение") }}</td>
                <td>{{ link_to("auctionsModer/delete/"~auction.getAuctionid(), "Удаление") }}</td>
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
                <li>{{ link_to("auctionsModer/index", "Первая") }}</li>
                <li>{{ link_to("auctionsModer/index?page="~page.before, "Предыдущая") }}</li>
                <li>{{ link_to("auctionsModer/index?page="~page.next, "Следующая") }}</li>
                <li>{{ link_to("auctionsModer/index?page="~page.last, "Последняя") }}</li>
            </ul>
        </nav>
    </div>
</div>

