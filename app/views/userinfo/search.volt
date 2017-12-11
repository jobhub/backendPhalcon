<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("userinfo/index", "Go Back") }}</li>
            <li class="next">{{ link_to("userinfo/new", "Create ") }}</li>
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
            <th>Firstname</th>
            <th>Patronymic</th>
            <th>Lastname</th>
            <th>Birthday</th>
            <th>Male</th>
            <th>Address</th>
            <th>About</th>
            <th>Executor</th>

                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
        {% for userinfo in page.items %}
            <tr>
                <td>{{ userinfo.userId }}</td>
            <td>{{ userinfo.firstname }}</td>
            <td>{{ userinfo.patronymic }}</td>
            <td>{{ userinfo.lastname }}</td>
            <td>{{ userinfo.birthday }}</td>
            <td>{{ userinfo.male }}</td>
            <td>{{ userinfo.address }}</td>
            <td>{{ userinfo.about }}</td>
            <td>{{ userinfo.executor }}</td>

                <td>{{ link_to("userinfo/edit/"~userinfo.userId, "Edit") }}</td>
                <td>{{ link_to("userinfo/delete/"~userinfo.userId, "Delete") }}</td>
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
                <li>{{ link_to("userinfo/search", "First") }}</li>
                <li>{{ link_to("userinfo/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("userinfo/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("userinfo/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>
