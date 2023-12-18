var AxbitEdsRegister = BX.namespace('AxbitEdsRegister');


// кнопка "Добавить подпись"
AxbitEdsRegister.showSignatureEditForm = function (id = 0) {

    if (AxbitEdsRegister.popupSignatureEditForm) {
        AxbitEdsRegister.popupSignatureEditForm.close();
        AxbitEdsRegister.popupSignatureEditForm.destroy();
    }

    var title = 'Создание новой записи';
    if (id > 0) {
        title = 'Редактирование записи с id=' + id;
    }

    // попап с формой редактирования/добавления цифровой подписи
    AxbitEdsRegister.popupSignatureEditForm = new BX.PopupWindow("popup-signature-edit-form", null, {
        content: '<div id="signature-edit-form-container">Подождите ...</div>',
        closeIcon: {right: "20px", top: "10px"},
        titleBar: {
            content: BX.create("p", {
                html: '<b>' + title + '</b>',
                'props': {'className': 'access-title-bar'}
            })
        },
        overlay: {
            backgroundColor: '#868d95', opacity: '80'
        },
        className: 'custom',
        zIndex: 0,
        offsetLeft: 0,
        offsetTop: -200,
        width: 550,
        buttons: [
            new BX.PopupWindowButton({
                text: "Сохранить",
                className: "popup-window-button popup-window-button-accept",
                events: {
                    click: function () {
                        // скрыть ошибки
                        BX('signature-edit-form-error-message').innerHTML = '';

                        // показать прелоадер
                        AxbitTools.showLoader();

                        let notificationsSettingsForm = BX('signature-edit-form');
                        let data = new FormData(notificationsSettingsForm);

                        // аякс запрос к компоненту axbit:eds.register
                        BX.ajax.runComponentAction('axbit:eds.register', 'editSignature', {
                            mode: 'ajax',
                            data: {
                                id: data.get('id'),
                                userId: data.get('user_id'),
                                registrationDate: data.get('registration_date'),
                                expiryDate: data.get('expiry_date'),
                            },
                        }).then(
                            function (response) {
                                // скрыть прелоадер
                                AxbitTools.hideLoader();
                                if (response.data.success) {
                                    AxbitEdsRegister.popupSignatureEditForm.close();
                                    AxbitTools.showMessagePopup('Запись успешно сохранена', '', 'Ok', true);
                                } else {
                                    if (response.data.error) {
                                        BX('signature-edit-form-error-message').innerHTML = response.data.error;
                                    }
                                }
                                console.log(response.data);
                            },
                            function (response) {
                                // скрыть прелоадер
                                AxbitTools.hideLoader();
                                alert('Ошибка загрузки формы редактирования');
                            }
                        );
                    }
                }
            }),
            // кнопка закрыть попап
            new BX.PopupWindowButton({
                text: "Отменить" ,
                className: "popup-window-button-cancel" ,
                events: {click: function(){
                        this.popupWindow.close();
                    }}
            })
        ]
    });

    BX.ajax.insertToNode('/include/forms/signatureEditForm.php', BX('signature-edit-form-container')); // функция ajax-загрузки контента из урла в #div
    AxbitEdsRegister.popupSignatureEditForm.show(); // появление окна
};