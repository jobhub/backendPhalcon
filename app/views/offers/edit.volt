<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("offers", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Edit offers
    </h1>
</div>

{{ content() }}

{{ form("offers/save", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldOfferid" class="col-sm-2 control-label">OfferId</label>
    <div class="col-sm-10">
        {{ text_field("offerId", "type" : "numeric", "class" : "form-control", "id" : "fieldOfferid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldUserid" class="col-sm-2 control-label">UserId</label>
    <div class="col-sm-10">
        {{ text_field("userId", "type" : "numeric", "class" : "form-control", "id" : "fieldUserid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Deadline</label>
    <div class="col-sm-10">
        {{ text_field("deadline", "size" : 30, "class" : "form-control", "id" : "fieldDeadline") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Description</label>
    <div class="col-sm-10">
        {{ text_area("description", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldDescription") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Price</label>
    <div class="col-sm-10">
        {{ text_field("price", "type" : "numeric", "class" : "form-control", "id" : "fieldPrice") }}
    </div>
</div>


{{ hidden_field("id") }}

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Send', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
