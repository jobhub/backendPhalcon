<div class="page-header">
    <h1>
        Пользователи
    </h1>
    <p>
        {{ link_to("users/new", "Создать пользователя") }}
    </p>
</div>

{{ content() }}

{{ form("users/index", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldUserid" class="col-sm-1 control-label">ID</label>
    <div class="col-sm-2">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>

    <label for="fieldEmail" class="col-sm-1 control-label">Email</label>
    <div class="col-sm-2">
        {{ text_field("email", "size" : 30, "class" : "form-control", "id" : "fieldEmail") }}
    </div>

    <label for="fieldPhone" class="col-sm-1 control-label">Телефон</label>
    <div class="col-sm-2">
        {{ text_field("phone", "size" : 30, "class" : "form-control", "id" : "fieldPhone") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldRole" class="col-sm-1 control-label">Роль</label>
    <div class="col-sm-2">
        {{ select_static("role",['':'','User':'Пользователь', 'Guests': 'Гость', 'Moderator':'Модератор'], "class" : "form-control", "id" : "fieldRole") }}
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
                <th>ID пользователя</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Имя</th>
            <th>Фамилия</th>
            <th>Дата рождения</th>
            <th>Исполнитель</th>
            <th>Роль</th>


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
            <td>{{ user.userinfo.getFirstname() }}</td>
            <td>{{ user.userinfo.getLastname() }}</td>
            <td>{{ user.userinfo.getBirthday() }}</td>
            {% if user.userinfo.getExecutor() == 1 %}
            <td> Исполнитель </td>
            {% else %}
            <td> Заказчик </td>
            {% endif  %}
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


