<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctions/index", "Go Back") }}</li>
            <li class="next">{{ link_to("auctions/new", "Create ") }}</li>
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
                <th>AuctionId</th>
            <th>TaskId</th>
            <th>SelectedOffer</th>
            <th>DateStart</th>
            <th>DateEnd</th>

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

                <td>{{ link_to("auctions/edit/"~auction.getAuctionid(), "Edit") }}</td>
                <td>{{ link_to("auctions/delete/"~auction.getAuctionid(), "Delete") }}</td>
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
                <li>{{ link_to("auctions/search", "First") }}</li>
                <li>{{ link_to("auctions/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("auctions/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("auctions/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
