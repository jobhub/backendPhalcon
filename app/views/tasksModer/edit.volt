<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("tasks", "Назад") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Изменение задания
    </h1>
</div>

{{ content() }}

{{ form("tasks/save", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">ID пользователя</label>
    <div class="col-sm-10">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ select('categoryId', categories, 'using':['categoryId', 'categoryName'],"useEmpty":true,"emptyValue":null,
        'emptyText':'',"class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ text_area("description", "size" : 30, "class" : "form-control", "id" : "fieldDescription") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Время завершения выполнения</label>
    <div class="col-sm-10">
        {{ date_field("deadline", "size" : 30, "class" : "form-control", "id" : "fieldDeadline") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Цена</label>
    <div class="col-sm-10">
        {{ text_field("price", "type" : "numeric", "class" : "form-control", "id" : "fieldPrice") }}
    </div>



{{ hidden_field("id") }}

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Изменить', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
