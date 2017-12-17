<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ link_to("auctions", "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>
        Тендер
    </h1>
</div>

{{ content() }}

{{ form("offers/new", "method":"post", "autocomplete" : "off", "class" : "form-horizontal") }}

<div class="form-group">
    <label for="fieldAuctionid" class="col-sm-2 control-label">Номер тендера</label>
    <div class="col-sm-10">
        {{ text_field("auctionId", "type" : "numeric", "class" : "form-control", "id" : "fieldAuctionid", 'readonly':'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldName" class="col-sm-2 control-label">Название</label>
    <div class="col-sm-10">
        {{ text_field("name", "size" : 50, "class" : "form-control", "id" : "fieldName", "readonly":'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldCategoryid" class="col-sm-2 control-label">Категория</label>
    <div class="col-sm-10">
        {{ select('categoryId',categories,"using":["categoryId","categoryName"],"class" : "form-control", "id" : "fieldCategoryid","readonly":'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDescription" class="col-sm-2 control-label">Описание</label>
    <div class="col-sm-10">
        {{ text_field("description", "size" : 1000, "class" : "form-control", "id" : "fieldDescription","readonly":'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldaddress" class="col-sm-2 control-label">Адрес</label>
    <div class="col-sm-10">
        {{ text_field("address", "size" : 100, "class" : "form-control", "id" : "fieldaddress","readonly":'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDeadline" class="col-sm-2 control-label">Дата работ</label>
    <div class="col-sm-10">
        {{ date_field("deadline","class":"form-control","id" : "fieldDeadline","readonly":'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldPrice" class="col-sm-2 control-label">Стоимость</label>
    <div class="col-sm-10">
        {{ text_field("price", "type" : "numeric", "class" : "form-control", "id" : "fieldPrice","readonly":'true') }}
    </div>
</div>

<div class="form-group">
    <label for="fieldDatestart" class="col-sm-2 control-label">Дата начала тендера</label>
    <div class="col-sm-10">
        {{ date_field("dateStart","class":"form-control","id" : "fieldDatestart",'readonly':'true' )}}
    </div>
</div>

<div class="form-group">
    <label for="fieldDateend" class="col-sm-2 control-label">Дата окончания тендера</label>
    <div class="col-sm-10">
        {{ date_field("dateEnd","class":"form-control","id" : "fieldDateend",'readonly':'true') }}
    </div>
</div>
</form>


<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>OfferId</th>
            <th>UserId</th>
            <th>AuctionId</th>
            <th>Deadline</th>
            <th>Description</th>
            <th>Price</th>

            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {% if page.items is defined %}
            {% for offer in page.items %}
                <tr>
                    <td>{{ offer.getOfferId() }}</td>
                    <td>{{ offer.getUserId() }}</td>
                    <td>{{ offer.getAuctionId() }}</td>
                    <td>{{ offer.getDeadline() }}</td>
                    <td>{{ offer.getDescription() }}</td>
                    <td>{{ offer.getPrice() }}</td>

                    <td>{{ link_to("auctions/choice/"~offer.getOfferId(), "Выбрать") }}</td>
                </tr>
            {% endfor %}
        {% endif %}
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-sm-1">
        <p class="pagination" style="line-height: 1.42857;padding: 6px 12px;">
            {{ page.current~"/"~page.total_pages }}
        </p>
    </div>
    <div class="col-sm-11">
        <nav>
            <ul class="pagination">
                <li>{{ link_to("auctions/search", "First") }}</li>
                <li>{{ link_to("auctions/search?page="~page.before, "Previous") }}</li>
                <li>{{ link_to("auctions/search?page="~page.next, "Next") }}</li>
                <li>{{ link_to("auctions/search?page="~page.last, "Last") }}</li>
            </ul>
        </nav>
    </div>
</div>