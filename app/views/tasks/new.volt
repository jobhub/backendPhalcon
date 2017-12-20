<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("tasks", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Создание задания
    </h1>
</div>

{{ content() }}

{{ form("tasks/create", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        {{ text_field("name", "size" : 50, "class" : "form-control", "id" : "fieldName") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ select('categoryId',categories,"using":["categoryId","categoryName"],"class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ text_area("description", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldDescription") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldaddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        {{ text_area("address","cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldaddress") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        {{ date_field("deadline","class":"form-control","id" : "fieldDeadline") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        {{ text_field("price", "type" : "numeric", "class" : "form-control", "id" : "fieldPrice") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Создать', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
