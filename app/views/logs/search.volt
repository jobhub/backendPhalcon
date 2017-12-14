<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("logs/index", "Go Back") }}</li>
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
                <th>LogId</th>
            <th>UserId</th>
            <th>Controller</th>
            <th>Action</th>
            <th>Date</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for log in page.items %}
            <tr>
                <td>{{ log.getLogid() }}</td>
            <td>{{ log.getUserid() }}</td>
            <td>{{ log.getController() }}</td>
            <td>{{ log.getAction() }}</td>
            <td>{{ log.getDate() }}</td>

                <td>{{ link_to("logs/edit/"~log.getLogid(), "Edit") }}</td>
                <td>{{ link_to("logs/delete/"~log.getLogid(), "Delete") }}</td>
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
                <li>{{ link_to("logs/search", "First") }}</li>
                <li>{{ link_to("logs/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("logs/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("logs/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
