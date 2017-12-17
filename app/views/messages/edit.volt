<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("messages", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Изменение сообщения
    </h1>
</div>

{{ content() }}

{{ form("messages/save", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">ID аукциона</label>
    <div class="col-sm-10">
        {{ text_field("auctionId", "type" : "numeric", "class" : "form-control", "id" : "fieldAuctionid") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldInput" class="col-sm-2 control-label">Тип сообщения</label>
    <div class="col-sm-10">
        {{ select_static ("input", ['1':'От исполнителя', '0':'От заказчика'], "class" : "form-control", "id" : "fieldInput") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldMessage" class="col-sm-2 control-label">Текст сообщения</label>
    <div class="col-sm-10">
        {{ text_area("message", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldMessage") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDate" class="col-sm-2 control-label">Дата и время отправки</label>
    <div class="col-sm-10">
        {{ date_field("date", "type" : "date", "class" : "form-control", "id" : "fieldDate") }}
    </div>
</div>


{{ hidden_field("id") }}

<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Send', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
