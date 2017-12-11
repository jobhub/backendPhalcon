<div class="page-header">
    <h1>
        Search logs
    </h1>
    <p>
        {{ link_to("logs/new", "Create logs") }}
    </p>
</div>

{{ content() }}

{{ form("logs/search", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldLogid" class="col-sm-2 control-label">LogId</label>
    <div class="col-sm-10">
        {{ text_field("logId", "type" : "numeric", "class" : "form-control", "id" : "fieldLogid") }}
    </div>
</div>

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
        {{ submit_button('Search', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
