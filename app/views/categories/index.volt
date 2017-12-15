<div class="page-header">
    <h1>
        Search categories
    </h1>
    <p>
        {{ link_to("categories/new", "Create categories") }}
    </p>
</div>

{{ content() }}

{{ form("categories/search", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">CategoryId</label>
    <div class="col-sm-10">
        {{ text_field("categoryId", "type" : "numeric", "class" : "form-control", "id" : "fieldCategoryid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryname" class="col-sm-2 control-label">CategoryName</label>
    <div class="col-sm-10">
        {{ text_field("categoryName", "size" : 30, "class" : "form-control", "id" : "fieldCategoryname") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Search', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
