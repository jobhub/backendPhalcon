<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("categories", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Create categories
    </h1>
</div>

{{ content() }}

{{ form("categories/create", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldCategoryname" class="col-sm-2 control-label">CategoryName</label>
    <div class="col-sm-10">
        {{ text_field("categoryName", "size" : 30, "class" : "form-control", "id" : "fieldCategoryname") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Save', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
