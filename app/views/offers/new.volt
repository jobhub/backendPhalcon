<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("offers", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Создать предложение
    </h1>
</div>

{{ content() }}

{{ form("offers/create", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldDescriptionOffer" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ text_area("descriptionOffer", "cols": "30", "rows": "4", "class" : "form-control", "id" : "fieldDescription") }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadlineOffer" class="col-sm-2 control-label">Срок выполнения</label>
    <div class="col-sm-10">
        {{ date_field("deadlineOffer", "class" : "form-control", "id" : "fieldDeadline") }}
    </div>
</div>



<div class="form-group">
    <label for="fieldPriceOffer" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        {{ text_field("priceOffer", "type" : "numeric", "class" : "form-control", "id" : "fieldPrice") }}
    </div>
</div>


<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        {{ submit_button('Добавить', 'class': 'btn btn-default') }}
    </div>
</div>

</form>
