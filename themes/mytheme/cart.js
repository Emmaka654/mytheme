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

        var productId = $(this).data('product-id');
        var quantity = 1;

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
        var id = item.id;
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