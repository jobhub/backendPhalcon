<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("logs", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Create logs
    </h1>
</div>

{{ content() }}

{{ form("logs/create", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">UserId</label>
    <div class="col-sm-10">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldController" class="col-sm-2 control-label">Controller</label>
    <div class="col-sm-10">
        {{ text_field("controller", "size" : 30, "class" : "form-control", "id" : "fieldController") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldAction" class="col-sm-2 control-label">Action</label>
    <div class="col-sm-10">
        {{ text_field("action", "size" : 30, "class" : "form-control", "id" : "fieldAction") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDate" class="col-sm-2 control-label">Date</label>
    <div class="col-sm-10">
        {{ text_field("date", "size" : 30, "class" : "form-control", "id" : "fieldDate") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Save', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
