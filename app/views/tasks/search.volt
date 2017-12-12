<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("tasks/index", "Go Back") }}</li>
            <li class="next">{{ link_to("tasks/new", "Create ") }}</li>
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
                <th>TaskId</th>
            <th>UserId</th>
            <th>CategoryId</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Price</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for task in page.items %}
            <tr>
                <td>{{ task.getTaskid() }}</td>
            <td>{{ task.getUserid() }}</td>
            <td>{{ task.getCategoryid() }}</td>
            <td>{{ task.getDescription() }}</td>
            <td>{{ task.getDeadline() }}</td>
            <td>{{ task.getPrice() }}</td>

                <td>{{ link_to("tasks/edit/"~task.getTaskid(), "Edit") }}</td>
                <td>{{ link_to("tasks/delete/"~task.getTaskid(), "Delete") }}</td>
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
