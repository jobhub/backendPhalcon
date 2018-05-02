
var myMap;

ymaps.ready(init);

function init() {

    var myPlacemark;

    myMap = new ymaps.Map("map", {
        center: [50.6,36.5],
        zoom: 17,
        controls: ['geolocationControl']
    }, {
        searchControlProvider: 'yandex#search'
    });
    myMap.behaviors.disable('scrollZoom');
    myMap.controls.add("zoomControl", {
        position: {top: 60, left: 15}
    });

    //определене местоположения пользователя
    ymaps.geolocation.get({
        // Выставляем опцию для определения положения по ip
        provider: 'auto',
        // Карта автоматически отцентрируется по положению пользователя.
        mapStateAutoApply: true
    }).then(function (result) {
        myMap.geoObjects.add(result.geoObjects);
        var coords=result.geoObjects.get(0).geometry.getCoordinates();
      //  console.log('Coords1',coords);
        coordText.value=coords;
        // Поиск по координатам
        ymaps.geocode(coords, {
            results: 1
        }).then(function (res) {
            // Задаем изображение для иконок меток.
            res.geoObjects.options.set('preset', 'islands#redCircleIcon');
            addressText.value = res.geoObjects.get(0).getAddressLine();
            // Добавляем коллекцию найденных геообъектов на карту.
           // myMap.geoObjects.add(res.geoObjects);
            // Масштабируем карту на область видимости коллекции.
            //myMap.setBounds(res.geoObjects.getBounds());
        });
        getAddress(coords);
    });






    // Создание метки.
    function createPlacemark(coords) {
        console.log('function: ','createPlacemark');
        return new ymaps.Placemark(coords, {
            iconCaption: 'поиск...'
        }, {
            preset: 'islands#violetDotIconWithCaption',
            draggable: true
        });
    }

    function getAddress(coords) {
        console.log('function: ','getAddress');
        myPlacemark.properties.set('iconCaption', 'поиск...');
        ymaps.geocode(coords).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            console.log('firstGeoObject: ',firstGeoObject);
            myPlacemark.properties
                .set({
                    // Формируем строку с данными об объекте.
                    iconCaption: [
                        // Название населенного пункта или вышестоящее административно-территориальное образование.
                        firstGeoObject.getLocalities().length ? firstGeoObject.getAddressLine() : firstGeoObject.getAdministrativeAreas(),
                        // Получаем путь до топонима, если метод вернул null, запрашиваем наименование здания.
                        firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                    ].filter(Boolean).join(', '),
                    // В качестве контента балуна задаем строку с адресом объекта.
                    balloonContent: firstGeoObject.getAddressLine()
                });
            console.log('firstGeoObject.getAddressLine: ',firstGeoObject.getAddressLine());
            addressText.value = firstGeoObject.getAddressLine();
        });

    }

function setMarkAddress(address) {
    ymaps.geocode(address,{
        result:1
    }).then(function (res) {
        // console.log(res);
        firstGeoObject = res.geoObjects.get(0),
            // Координаты геообъекта.
            coords = firstGeoObject.geometry.getCoordinates(),
            // Область видимости геообъекта.
            bounds = firstGeoObject.properties.get('boundedBy');
        console.log('firstGeoObject: ',firstGeoObject);
        console.log('Сменить координаты метки на: ',coords);
        if(myPlacemark)
        {
            myPlacemark.options.set('preset', 'islands#darkBlueDotIconWithCaption');
            // Получаем строку с адресом и выводим в иконке геообъекта.
            myPlacemark.properties.set('iconCaption', firstGeoObject.getAddressLine());
            myPlacemark.properties.set('balloonContent',firstGeoObject.getAddressLine());
            myPlacemark.geometry.setCoordinates(coords);
            myMap.setBounds(bounds, {
                // Проверяем наличие тайлов на данном масштабе.
                checkZoomRange: true

            });
            console.log('Текущие координаты метки: ', myPlacemark.geometry.getCoordinates());
        }
        else
        {
            myPlacemark = createPlacemark(coords);
            console.log('myPlacemark: ',myPlacemark);
            myPlacemark.options.set('preset', 'islands#darkBlueDotIconWithCaption');
            // Получаем строку с адресом и выводим в иконке геообъекта.
            myPlacemark.properties.set('iconCaption', firstGeoObject.getAddressLine());
            myPlacemark.properties.set('balloonContent',firstGeoObject.getAddressLine());
            myMap.setBounds(bounds, {
                // Проверяем наличие тайлов на данном масштабе.
                checkZoomRange: true
            });
            myMap.geoObjects.add(myPlacemark);
            // Слушаем событие окончания перетаскивания на метке.
            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
        }
    })
}

}
function setMarks(tasks) {

    ymaps.ready(function() {
        console.log('setMarks: ', tasks);
       /* console.log('Coord1: ', tasks[2].coords);
        console.log('Coord2: ', tasks[1].coords);
        placemark1ForMap1 = new ymaps.Placemark(tasks[2].coords);
        placemark1ForMap2 = new ymaps.Placemark(tasks[1].coords);
        console.log('placemark1: ', placemark1ForMap1);
        console.log('placemark2: ', placemark1ForMap2);

        myMap.geoObjects.add(placemark1ForMap1);
        myMap.geoObjects.add(placemark1ForMap2);*/

        /*for (var i = 0; i < tasks.length; i++) {
            var center = tasks[i].latitude + ',' + tasks[i].longitude;
            placemark = new ymaps.Placemark(center,
                {balloonContent: tasks[i].name});
        }
        myMap.setBounds(myMap.geoObjects.getBounds());*/


        for(var i=0;i<tasks.length;i++)
        {
          //  console.log('Coord'+i+': ', tasks[i].coords);
            placemark=new ymaps.Placemark(tasks[i].coords);
            placemark.options.set('preset', 'islands#darkBlueDotIconWithCaption');
            placemark.properties.set('iconCaption', tasks[i].name);
            var content='<div>Описание: '+tasks[i].description+'</div>'+
                '<div>Адрес: '+tasks[i].address+'</div>'+
                '<div>Стоимость: '+tasks[i].price+'</div>'+
                '<div>Дата: '+tasks[i].deadline+'</div>'+
                '<div>Дата конца тендера:'+tasks[i].dateEnd+'</div>'+
                '<div><a href='+'"'+tasks[i].link+'"'+'>Ссылка на тендер'+'</a></div>';
            console.log('content'+i+': ',content);
            placemark.properties.set('balloonContent',content);


           //console.log('name'+i+': ',tasks[i].name);
           // console.log('name'+i+': ', placemark.name);
           // console.log('description'+i+': ',tasks[i].description);
           // console.log('description'+i+': ',placemark.description);
          //  console.log('placemark'+i+': ', placemark);
            var err=myMap.geoObjects.add(placemark);

          //  console.log('err'+i+': ', err);
        }
        myMap.setBounds(myMap.geoObjects.getBounds());
    }
);
}