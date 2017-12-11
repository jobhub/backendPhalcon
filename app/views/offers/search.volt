<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("offers/index", "Go Back") }}</li>
            <li class="next">{{ link_to("offers/new", "Create ") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>Search result</h1>
</div>

{{ content() }}

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>OfferId</th>
            <th>UserId</th>
            <th>Deadline</th>
            <th>Description</th>
            <th>Price</th>

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

                <td>{{ link_to("offers/edit/"~offer.getOfferid(), "Edit") }}</td>
                <td>{{ link_to("offers/delete/"~offer.getOfferid(), "Delete") }}</td>
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
                <li>{{ link_to("offers/search", "First") }}</li>
                <li>{{ link_to("offers/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("offers/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("offers/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
