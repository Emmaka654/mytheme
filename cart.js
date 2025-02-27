function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}


function getCookie(name) {
    return document.cookie.split('; ').reduce((r, v) => {
        const parts = v.split('=');
        return parts[0] === name ? decodeURIComponent(parts[1]) : r;
    }, '');
}

function deleteCookie(name) {
    setCookie(name, '', -1);
}

document.addEventListener('DOMContentLoaded', function () {
    const cartIcon = document.querySelector('#cart-icon');
    const cartPopup = document.querySelector('#cart-popup');
    const closeCartPopup = document.querySelector('#close-cart-popup');

    // Показать или скрыть всплывающее окно при нажатии на иконку корзины
    cartIcon.addEventListener('click', function () {

        cartPopup.style.display = cartPopup.style.display === 'block' ? 'none' : 'block';
        updateCartDisplay();
    });

    // Закрыть всплывающее окно
    closeCartPopup.addEventListener('click', function () {
        cartPopup.style.display = 'none';
    });
});

document.addEventListener('DOMContentLoaded', updateCartDisplay);
jQuery(document).ready(function ($) {
    $('.add-to-cart-button').on('click', function (e) {
        e.preventDefault();

        const productId = $(this).data('product-id');
        const quantity = 1;

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'add_to_cart',
                product_id: productId,
                quantity: quantity
            },
            success: function (response) {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                if (response.success) {
                    alert('Товар добавлен в корзину: ' + response.data.product_id + ' (Количество: ' + response.data.quantity + ')');
                    updateCartDisplay();
                } else {
                    alert('Ошибка: ' + response.data);
                }
            },
            error: function () {
                alert('Произошла ошибка при добавлении товара в корзину.');
            }
        });
    });
});

function updateCartDisplay() {
    const cartCookie = getCookie('cart') || '{}';
    const cart = JSON.parse(cartCookie);
    const productIds = Object.keys(cart).join(',');

    if (productIds.length === 0) {
        const messageContainer = document.querySelector('#cart-items');
        messageContainer.innerHTML = "Корзина пуста.";
        const itemCountElement = document.querySelector('#cart-count');
        itemCountElement.textContent = "0";
        return;
    }

    jQuery.ajax({
        url: ajax_object.ajax_url,
        method: 'POST',
        data: {
            action: 'get_cart_items',
            ids: productIds,
            quantities: JSON.stringify(cart) // Передаем количество товаров
        },
        success: function (response) {
            if (typeof response === 'string') {
                response = JSON.parse(response);
            }
            if (response.success) {
                displayCartItems(response.data);
            } else {
                console.error('Ошибка в ответе:', response ? response.data : 'Ответ не содержит данных');
            }
        },
        error: function () {
            alert('Произошла ошибка при выводе корзины.');
        }
    });
}

// Функция для отображения товаров в корзине
function displayCartItems(items) {
    const cartContainer = document.querySelector('#cart-items');
    const itemCountElement = document.querySelector('#cart-count');
    cartContainer.innerHTML = '';

    const totalItems = items.reduce((total, item) => total + item.quantity, 0);
    itemCountElement.textContent = totalItems;
    let totalPrice = 0; // Переменная для расчета общей стоимости

    const list = document.createElement('ul');
    items.forEach(item => {
        const listItem = document.createElement('li');
        const id = item.id;
        listItem.textContent = 'Товар: ' + item.title + ', Цена: ' + item.price + ', Количество: ' + item.quantity;
        list.appendChild(listItem);

        // Кнопки для изменения количества
        const increaseButton = document.createElement('button');
        increaseButton.textContent = '+';
        increaseButton.addEventListener('click', () => updateQuantity(id, 1));
        listItem.appendChild(increaseButton);

        const decreaseButton = document.createElement('button');
        decreaseButton.textContent = '-';
        decreaseButton.addEventListener('click', () => updateQuantity(id, -1));
        listItem.appendChild(decreaseButton);

        // Кнопка для удаления товара
        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Удалить';
        deleteButton.addEventListener('click', () => removeItem(id));
        listItem.appendChild(deleteButton);

        totalPrice += item.price * item.quantity; // Расчет общей стоимости
    });

    cartContainer.appendChild(list);

    // Отображение общей стоимости
    const totalPriceElement = document.createElement('p');
    totalPriceElement.textContent = "Общая стоимость: " + totalPrice.toFixed(2);
    cartContainer.appendChild(totalPriceElement);
}

// Функция для изменения количества товара
function updateQuantity(productId, change) {
    let cartCookie = getCookie('cart');
    let cart = cartCookie ? JSON.parse(cartCookie) : [];

    cart[productId] += change;

    // Если количество меньше или равно 0, удаляем товар
    if (cart[productId] <= 0) {
        removeItem(productId); // Удаляем товар из корзины
    } else {

        // Сохраняем обновленный cart обратно в cookie
        setCookie('cart', JSON.stringify(cart), {path: '/'});
        updateCartDisplay();
    }
}


// Функция для удаления товара из корзины
function removeItem(productId) {
    let cartCookie = getCookie('cart');
    let cart = cartCookie ? JSON.parse(cartCookie) : [];

    delete cart[productId];
    cart = Object.fromEntries(Object.entries(cart).filter(([_, v]) => v > 0));

    setCookie('cart', JSON.stringify(cart), 7); // Обновляем куки
    updateCartDisplay();// Обновляем отображение корзины
}

document.addEventListener('DOMContentLoaded', function () {
    const checkoutButton = document.querySelector('#checkout-button');
    const orderForm = document.querySelector('#order-form');
    const closeForm = document.getElementById('close-form');

    checkoutButton.addEventListener('click', function () {
        orderForm.style.display = orderForm.style.display === 'block' ? 'none' : 'block';
    });

    closeForm.addEventListener('click', function () {
        orderForm.style.display = 'none';
    });
});

jQuery(document).ready(function ($) {
    $('#checkout-button').on('click', function (e) {
        e.preventDefault();

        // Получаем email зарегистрированного пользователя
        $.ajax({
            url: ajax_object.ajax_url,
            method: 'GET',
            data: {
                action: 'get_user_email' // Имя действия для обработки на сервере
            },
            success: function (response) {
                if (response.success) {
                    // Заполняем поле email в форме
                    $('#email_user').val(response.data.email);
                } else {
                    console.error('Ошибка получения email:', response.data.message);
                }
            },
            error: function () {
                console.error('AJAX ошибка:', error);
            }
        });
    });

    $('#submit-button').on('click', function (e) {
        e.preventDefault();

        const formData = $(this).closest('form').serialize();
        const cartCookie = getCookie('cart') || '{}';
        const cart = JSON.parse(cartCookie);

        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'submit_order', // Имя действия для обработки на сервере
                formData: formData, // Данные формы
                cart: cart // Данные о товарах в корзине
            },
            success: function (response) {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                if (response.success) {
                    alert(response.message); // Выводим ответ от сервера
                    $('#order-form')[0].reset(); // Сбрасываем форму
                    deleteCookie('cart');
                    updateCartDisplay();
                    document.querySelector('#order-form').style.display = 'none';
                } else {
                    alert('Произошла ошибка. Проверьте, выбрали, ли вы товары. А также ввели ли вы все данные');
                }
            },
            error: function () {
                alert('Произошла ошибка. Попробуйте еще раз.');
            }
        });
    });
});