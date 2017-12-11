<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctions", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Edit auctions
    </h1>
</div>

{{ content() }}

{{ form("auctions/save", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

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


{{ hidden_field("id") }}

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Send', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
