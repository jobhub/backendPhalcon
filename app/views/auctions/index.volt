<div class="page-header">
    <h1>
        Search auctions
    </h1>
    <p>
        {{ link_to("auctions/new", "Create auctions") }}
    </p>
</div>

{{ content() }}

{{ form("auctions/search", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">AuctionId</label>
    <div class="col-sm-10">
        {{ text_field("auctionId", "type" : "numeric", "class" : "form-control", "id" : "fieldAuctionid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldTaskid" class="col-sm-2 control-label">TaskId</label>
    <div class="col-sm-10">
        {{ text_field("taskId", "type" : "numeric", "class" : "form-control", "id" : "fieldTaskid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldSelectedoffer" class="col-sm-2 control-label">SelectedOffer</label>
    <div class="col-sm-10">
        {{ text_field("selectedOffer", "type" : "numeric", "class" : "form-control", "id" : "fieldSelectedoffer") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">DateStart</label>
    <div class="col-sm-10">
        {{ text_field("dateStart", "size" : 30, "class" : "form-control", "id" : "fieldDatestart") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">DateEnd</label>
    <div class="col-sm-10">
        {{ text_field("dateEnd", "size" : 30, "class" : "form-control", "id" : "fieldDateend") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Search', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
