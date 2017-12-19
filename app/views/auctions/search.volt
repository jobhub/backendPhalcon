<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctions/index", "Go Back") }}</li>
            <li class="next">{{ link_to("auctions/new", "Создать ") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>Поиск тендеров</h1>
</div>

{{ content() }}

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>№ Тендера</th>
            <th>№ задания</th>
            <th>Выбранное предложение</th>
            <th>Дата начала</th>
            <th>Дата конца</th>

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

                <td>{{ link_to("auctions/edit/"~auction.getAuctionid(), "Редактировать") }}</td>
                <td>{{ link_to("auctions/delete/"~auction.getAuctionid(), "Удалить") }}</td>
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
