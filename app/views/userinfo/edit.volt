<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("userinfo", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Edit userinfo
    </h1>
</div>

{{ content() }}

{{ form("userinfo/save", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}



<div class="form-group">
    <label for="fieldFirstname" class="col-sm-2 control-label">Firstname</label>
    <div class="col-sm-10">
        {{ text_field("firstname", "size" : 30, "class" : "form-control", "id" : "fieldFirstname") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPatronymic" class="col-sm-2 control-label">Patronymic</label>
    <div class="col-sm-10">
        {{ text_field("patronymic", "size" : 30, "class" : "form-control", "id" : "fieldPatronymic") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldLastname" class="col-sm-2 control-label">Lastname</label>
    <div class="col-sm-10">
        {{ text_field("lastname", "size" : 30, "class" : "form-control", "id" : "fieldLastname") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldBirthday" class="col-sm-2 control-label">Birthday</label>
    <div class="col-sm-10">
        {{ text_field("birthday", "type" : "date", "class" : "form-control", "id" : "fieldBirthday") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldMale" class="col-sm-2 control-label">Male</label>
    <div class="col-sm-10">
        {{ text_field("male", "type" : "numeric", "class" : "form-control", "id" : "fieldMale") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldMale" class="col-sm-2 control-label">Male</label>
    <div class="col-sm-10">
        {{ text_field("male", "type" : "numeric", "class" : "form-control", "id" : "fieldMale") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldAddress" class="col-sm-2 control-label">Address</label>
    <div class="col-sm-10">
        {{ text_field("address", "size" : 30, "class" : "form-control", "id" : "fieldAddress") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldAbout" class="col-sm-2 control-label">About</label>
    <div class="col-sm-10">
        {{ text_area("about", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldAbout") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldExecutor" class="col-sm-2 control-label">Executor</label>
    <div class="col-sm-10">
        {{ text_field("executor", "type" : "numeric", "class" : "form-control", "id" : "fieldExecutor") }}
    </div>
</div>


{{ hidden_field("id") }}

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Send', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
