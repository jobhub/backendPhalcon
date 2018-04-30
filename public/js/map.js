ymaps.ready(init);

function init() {





    var myMap;
    var myPlacemark;
    var coordText = document.getElementById('fieldCoord');
    var addressText = document.getElementById('fieldAddress');




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
       // getAddress(coords);
    });



// добавление метки по клику
    myMap.events.add('click', function (e) {
        var coords = e.get('coords');
        console.log('Click',coords);
        coordText.value = coords;

        // Если метка уже создана – просто передвигаем ее.
        if (myPlacemark) {
            myPlacemark.geometry.setCoordinates(coords);
        }
        // Если нет – создаем.
        else {
            myPlacemark = createPlacemark(coords);
            console.log('myPlacemark: ',myPlacemark);
            myMap.geoObjects.add(myPlacemark);
            // Слушаем событие окончания перетаскивания на метке.
            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
        }
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


$('#fieldAddress').on('input', function () {
    console.log('function: ','onInput');
    setTimeout(function () {
        console.log('function: ','setTimeout');
        setMarkAddress(addressText.value);
    },5000);
});


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





    // var myMap1 = new ymaps.Map('map1', {
    //     center: [55.753994, 37.622093],
    //     zoom: 9
    // });
    // var firstGeoObject;
    // setInterval(lookForAddressChange, 2000);
    //
    // function lookForAddressChange( ) {
    //     // Поиск координат центра Нижнего Новгорода.
    //     ymaps.geocode(addressText.value, {
    //         /**
    //          * Опции запроса
    //          * @see https://api.yandex.ru/maps/doc/jsapi/2.1/ref/reference/geocode.xml
    //          */
    //         // Сортировка результатов от центра окна карты.
    //         // boundedBy: myMap.getBounds(),
    //         // strictBounds: true,
    //         // Вместе с опцией boundedBy будет искать строго внутри области, указанной в boundedBy.
    //         // Если нужен только один результат, экономим трафик пользователей.
    //         results: 1
    //     }).then(function (res) {
    //         // Выбираем первый результат геокодирования.
    //          firstGeoObject = res.geoObjects.get(0),
    //             // Координаты геообъекта.
    //             coords = firstGeoObject.geometry.getCoordinates(),
    //             // Область видимости геообъекта.
    //             bounds = firstGeoObject.properties.get('boundedBy');
    //
    //         firstGeoObject.options.set('preset', 'islands#darkBlueDotIconWithCaption');
    //         // Получаем строку с адресом и выводим в иконке геообъекта.
    //         firstGeoObject.properties.set('iconCaption', firstGeoObject.getAddressLine());
    //
    //         // Добавляем первый найденный геообъект на карту.
    //         myMap1.geoObjects.add(firstGeoObject);
    //         // Масштабируем карту на область видимости геообъекта.
    //         myMap1.setBounds(bounds, {
    //             // Проверяем наличие тайлов на данном масштабе.
    //             checkZoomRange: true
    //         });
    //
    //         /**
    //          * Все данные в виде javascript-объекта.
    //          */
    //       //  console.log('Все данные геообъекта: ', firstGeoObject.properties.getAll());
    //         /**
    //          * Метаданные запроса и ответа геокодера.
    //          * @see https://api.yandex.ru/maps/doc/geocoder/desc/reference/GeocoderResponseMetaData.xml
    //          */
    //       //  console.log('Метаданные ответа геокодера: ', res.metaData);
    //         /**
    //          * Метаданные геокодера, возвращаемые для найденного объекта.
    //          * @see https://api.yandex.ru/maps/doc/geocoder/desc/reference/GeocoderMetaData.xml
    //          */
    //      //   console.log('Метаданные геокодера: ', firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData'));
    //         /**
    //          * Точность ответа (precision) возвращается только для домов.
    //          * @see https://api.yandex.ru/maps/doc/geocoder/desc/reference/precision.xml
    //          */
    //       //  console.log('precision', firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.precision'));
    //         /**
    //          * Тип найденного объекта (kind).
    //          * @see https://api.yandex.ru/maps/doc/geocoder/desc/reference/kind.xml
    //          */
    //       //  console.log('Тип геообъекта: %s', firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.kind'));
    //       //  console.log('Название объекта: %s', firstGeoObject.properties.get('name'));
    //       //  console.log('Описание объекта: %s', firstGeoObject.properties.get('description'));
    //       //  console.log('Полное описание объекта: %s', firstGeoObject.properties.get('text'));
    //         /**
    //          * Прямые методы для работы с результатами геокодирования.
    //          * @see https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/GeocodeResult-docpage/#getAddressLine
    //          */
    //        // console.log('\nГосударство: %s', firstGeoObject.getCountry());
    //        // console.log('Населенный пункт: %s', firstGeoObject.getLocalities().join(', '));
    //        // console.log('Адрес объекта: %s', firstGeoObject.getAddressLine());
    //        // console.log('Наименование здания: %s', firstGeoObject.getPremise() || '-');
    //        // console.log('Номер здания: %s', firstGeoObject.getPremiseNumber() || '-');
    //
    //         /**
    //          * Если нужно добавить по найденным геокодером координатам метку со своими стилями и контентом балуна, создаем новую метку по координатам найденной и добавляем ее на карту вместо найденной.
    //          */
    //         /**
    //          var myPlacemark = new ymaps.Placemark(coords, {
    //          iconContent: 'моя метка',
    //          balloonContent: 'Содержимое балуна <strong>моей метки</strong>'
    //          }, {
    //          preset: 'islands#violetStretchyIcon'
    //          });
    //
    //          myMap.geoObjects.add(myPlacemark);
    //          */
    //     });
    // }

}