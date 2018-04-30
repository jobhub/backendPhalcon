ymaps.ready(init);

function init() {





    var myMap;
    var myPlacemark;
    var coordText = document.getElementById('fieldCoord');
    var addressText = document.getElementById('fieldaddress');
    var coords=coordText.textContent;
    console.log('cor',coords);
    var address=addressText.textContent;
    console.log('addr',address);
    coords=coords.split(',');
    console.log('cor',coords);
    myMap = new ymaps.Map("map", {
        center: coords,
        zoom: 17,
        controls: ['geolocationControl']
    }, {
        searchControlProvider: 'yandex#search'
    });
    myMap.behaviors.disable('scrollZoom');
    myMap.controls.add("zoomControl", {
        position: {top: 60, left: 15}
    });
    setMarkAddress(address);

/*
    ymaps.geocode(address,{
        result:1
    }).then(function (res) {
        // console.log(res);
        firstGeoObject = res.geoObjects.get(0),
            // Координаты геообъекта.
            coords = firstGeoObject.geometry.getCoordinates(),
            // Область видимости геообъекта.
            bounds = firstGeoObject.properties.get('boundedBy');
        //console.log('Сменить координаты метки на: ',coords);
        if(myPlacemark)
        {
            myPlacemark.options.set('preset', 'islands#darkBlueDotIconWithCaption');
            // Получаем строку с адресом и выводим в иконке геообъекта.
            myPlacemark.properties.set('iconCaption', firstGeoObject.getAddressLine());
            myPlacemark.geometry.setCoordinates(coords);
            myMap.setBounds(bounds, {
                // Проверяем наличие тайлов на данном масштабе.
                checkZoomRange: true
            });
           // console.log('Текущие координаты метки: ', myPlacemark.geometry.getCoordinates());
        }
    })*/

    // Создание метки.
    function createPlacemark(coords) {
        return new ymaps.Placemark(coords, {
            iconCaption: address
        }, {
            preset: 'islands#violetDotIconWithCaption',
            draggable: true
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