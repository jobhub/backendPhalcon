<div class="page-header">
    <h1>
        Созданные задания
    </h1>
    <p> {{ link_to("tasks/new", "Создать задание") }}</p>
    <p> {{ link_to("tasks/mytasks/"~userId, "Мои задания") }}</p>
    <p>  {{ link_to("offers/myoffers/"~userId, "Мои предложения") }}</p>
    <p>  {{ link_to("tasks/doingtasks/"~userId, "Выполняемые задания") }}</p>
</div>

{{ content() }}

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Номер предложения</th>
            <th>Аукцион</th>
            <th>Описание</th>
            <th>Сроки</th>
            <th>Стоимость</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for offers in page.items %}

            <tr>
                <td>{{ offers.getOfferId() }}</td>
            <td>{{link_to("auctions/viewing/"~offers.getAuctionId(), "Аукцион") }}</td>
            <td>{{ offers.getDescription() }}</td>
            <td>{{ offers.getDeadline() }}</td>
            <td>{{ offers.getPrice() }}</td>

                <td>{{ link_to("offers/editing/"~offers.getOfferId(), "Редактировать") }}</td>
                <td>{{ link_to("offers/deleting/"~offers.getOfferId(), "Удалить") }}</td>
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
