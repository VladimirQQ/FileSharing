console.log('JS загружен и работает');

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS загружен и работает');

    // Обработчик для кнопки генерации ссылки
    document.querySelectorAll('.generate-link-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const fileId = this.dataset.fileId;
            generateFileLink(fileId);
        });
    });

    // Обработчик для кнопки копирования
    document.querySelectorAll('.copy-link-btn').forEach(btn => {
        btn.addEventListener('click', copyToClipboard);
    });
});

async function generateFileLink(fileId) {
    const button = document.querySelector(`.generate-link-btn[data-file-id="${fileId}"]`);
    if (!button) {
        const errorMsg = `Кнопка для fileId=${fileId} не найдена в DOM`;
        console.error(errorMsg);
        showToast(errorMsg, 'danger');
        return;
    }

    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Генерация...';
    button.disabled = true;

    try {
        // 1. Проверка CSRF-токена
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            throw new Error('CSRF-токен не найден. Обновите страницу (F5)');
        }

        // 2. Отправка запроса с обработкой сетевых ошибок
        let response;
        try {
            response = await fetch(`/files/${fileId}/generate-link`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
        } catch (networkError) {
            throw new Error(`Сетевая ошибка: ${networkError.message}`);
        }

        // 3. Обработка HTTP-статусов
        if (!response.ok) {
            const errorText = await response.text().catch(() => 'Не удалось прочитать ошибку');
            throw new Error(`HTTP ${response.status}: ${errorText || 'Неизвестная ошибка сервера'}`);
        }

        // 4. Парсинг JSON с проверкой структуры
        const data = await response.json().catch(() => {
            throw new Error('Сервер вернул некорректные данные (не JSON)');
        });

        if (!data.token) {
            console.error('Полученные данные:', data);
            throw new Error('Сервер не вернул токен в ответе');
        }

        // 5. Генерация полной ссылки для скачивания
        const downloadUrl = `${window.location.origin}/download/${data.token}`;
        
        // // 6. Отображение результата
        // const modalElement = document.getElementById('linkModal');
        // if (!modalElement) {
        //     throw new Error('Модальное окно не найдено на странице');
        // }

        const linkInput = document.getElementById('generatedLinkInput');
        const passwordInput = document.getElementById('generatedPassword');
        if (!linkInput || !passwordInput) {
            throw new Error('Не найдены поля для отображения ссылки');
        }

        linkInput.value = downloadUrl; // Используем сгенерированную ссылку
        passwordInput.value = data.password || '';

        // Инициализация модального окна Bootstrap 5
        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modal.show();

    } catch (error) {
        console.error('Полная ошибка:', {
            message: error.message,
            stack: error.stack,
            fileId: fileId,
            time: new Date().toISOString()
        });
        
        showToast(
            `Ошибка генерации: ${error.message}`,
            'danger',
            5000
        );
    } finally {
        button.innerHTML = originalContent;
        button.disabled = false;
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `position-fixed bottom-0 end-0 m-3 alert alert-${type}`;
    toast.style.zIndex = '1100';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

function copyToClipboard() {
    const input = document.getElementById('generatedLinkInput');
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        showToast('Ссылка скопирована!', 'success');
    } catch (err) {
        console.error('Ошибка при копировании:', err);
        showToast('Не удалось скопировать', 'danger');
    }
}

async function refreshFileLinks(fileId) {
    try {
        const response = await fetch(window.location.href);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById(`links-${fileId}`).innerHTML;
        document.getElementById(`links-${fileId}`).innerHTML = newContent;
    } catch (error) {
        console.error('Ошибка обновления списка:', error);
    }
}