<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctions", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Просмотр Тендера
    </h1>
</div>

{{ content() }}

{{ form("offers/new", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        {{ task.getName() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ task.categories.getCategoryName() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ task.getDescription() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldaddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        {{ task.getAddress() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        {{ task.getDeadline() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        {{ task.getPrice() }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">Дата начала тендера</label>
    <div class="col-sm-10">
        {{ auction.getDateStart()}}
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">Дата окончания тендера</label>
    <div class="col-sm-10">
        {{ auction.getDateEnd() }}
    </div>
</div>



<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Вступить', 'class': 'btn btn-default') }}
        <td>{{ link_to("userinfo/viewprofile/"~auction.tasks.getUserId(), "Профиль") }}</td>
    </div>

</div>

</form>
