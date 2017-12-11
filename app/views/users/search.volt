<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("users/index", "Go Back") }}</li>
            <li class="next">{{ link_to("users/new", "Create ") }}</li>
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
                <th>UserId</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Password</th>
            <th>Role</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for user in page.items %}
            <tr>
                <td>{{ user.getUserid() }}</td>
            <td>{{ user.getEmail() }}</td>
            <td>{{ user.getPhone() }}</td>
            <td>{{ user.getPassword() }}</td>
            <td>{{ user.getRole() }}</td>

                <td>{{ link_to("users/edit/"~user.getUserid(), "Edit") }}</td>
                <td>{{ link_to("users/delete/"~user.getUserid(), "Delete") }}</td>
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
                <li>{{ link_to("users/search", "First") }}</li>
                <li>{{ link_to("users/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("users/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("users/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
