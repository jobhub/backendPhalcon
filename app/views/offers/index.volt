<div class="page-header">
    <h1>
        Search offers
    </h1>
    <p>
        {{ link_to("offers/new", "Create offers") }}
    </p>
</div>

{{ content() }}

{{ form("offers/search", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

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


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Search', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
