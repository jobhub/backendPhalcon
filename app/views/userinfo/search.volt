<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("user_info/index", "Go Back") }}</li>
            <li class="next">{{ link_to("user_info/new", "Create ") }}</li>
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
                <th>Firstname</th>
            <th>Lastname</th>
            <th>Birthday</th>
            <th>Male</th>
            <th>Address</th>
            <th>About</th>
            <th>Executor</th>
            <th>User</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for user_info in page.items %}
            <tr>
                <td>{{ user_info.firstname }}</td>
            <td>{{ user_info.lastname }}</td>
            <td>{{ user_info.birthday }}</td>
            <td>{{ user_info.male }}</td>
            <td>{{ user_info.address }}</td>
            <td>{{ user_info.about }}</td>
            <td>{{ user_info.executor }}</td>
            <td>{{ user_info.user_id }}</td>

                <td>{{ link_to("user_info/edit/"~user_info.firstname, "Edit") }}</td>
                <td>{{ link_to("user_info/delete/"~user_info.firstname, "Delete") }}</td>
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
                <li>{{ link_to("user_info/search", "First") }}</li>
                <li>{{ link_to("user_info/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("user_info/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("user_info/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
