// Валидация форм
document.addEventListener('DOMContentLoaded', function() {
    // Функции валидации
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validatePhone(phone) {
        const re = /^[\d\s\-\+\(\)]{10,}$/;
        return re.test(phone.replace(/\s/g, ''));
    }

    function showError(field, message) {
        field.classList.add('invalid');
        let errorElement = field.parentNode.querySelector('.validation-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'validation-error';
            field.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
        return false;
    }

    function clearError(field) {
        field.classList.remove('invalid');
        const errorElement = field.parentNode.querySelector('.validation-error');
        if (errorElement) {
            errorElement.remove();
        }
        return true;
    }

    function validateField(field) {
        const value = field.value.trim();
        
        if (field.hasAttribute('required') && !value) {
            return showError(field, 'Это поле обязательно для заполнения');
        }
        
        if (field.type === 'email' && value && !validateEmail(value)) {
            return showError(field, 'Введите корректный email адрес');
        }
        
        if (field.type === 'tel' && value && !validatePhone(value)) {
            return showError(field, 'Введите корректный номер телефона');
        }
        
        return clearError(field);
    }

    // Реальная валидация при вводе
    const emailFields = document.querySelectorAll('input[type="email"]');
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    const requiredFields = document.querySelectorAll('input[required], textarea[required]');
    
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                validateField(this);
            }
        });
    });
    
    phoneFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                validateField(this);
            }
        });
    });
    
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
    });

    // Валидация при отправке формы
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const fieldsToValidate = this.querySelectorAll('input, textarea');
            
            fieldsToValidate.forEach(field => {
                if (!validateField(field)) {
                    valid = false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                // Прокручиваем к первой ошибке
                const firstError = this.querySelector('.invalid');
                if (firstError) {
                    firstError.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    firstError.focus();
                }
            }
        });
    });

    // Автоматическое скрытие сообщений через 5 секунд
    const messages = document.querySelectorAll('.alert-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.5s';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });

    // Подтверждение удаления с улучшенным UI
    // const deleteButtons = document.querySelectorAll('.btn-delete');
    // deleteButtons.forEach(button => {
    //     button.addEventListener('click', function(e) {
    //         if (!confirm(this.getAttribute('data-confirm') || 'Вы уверены, что хотите удалить этот контакт?')) {
    //             e.preventDefault();
    //         }
    //     });
    // });

    // Динамическое обновление счетчика символов для textarea
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        if (maxLength) {
            const counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.style.fontSize = '12px';
            counter.style.color = '#6c757d';
            counter.style.textAlign = 'right';
            counter.style.marginTop = '5px';
            textarea.parentNode.appendChild(counter);
            
            function updateCounter() {
                const currentLength = textarea.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;
                
                if (currentLength > maxLength * 0.9) {
                    counter.style.color = '#dc3545';
                } else {
                    counter.style.color = '#6c757d';
                }
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    });

    // Улучшение UX для загрузки файлов
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        const container = input.closest('.form-group');
        if (container) {
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-info';
            fileInfo.style.marginTop = '8px';
            fileInfo.style.fontSize = '14px';
            fileInfo.style.color = '#6c757d';
            container.appendChild(fileInfo);
            
            input.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    fileInfo.innerHTML = `
                        <strong>Выбран файл:</strong> ${file.name}<br>
                        <small>Размер: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    `;
                } else {
                    fileInfo.innerHTML = 'Файл не выбран';
                }
            });
            
            // Инициализация
            fileInfo.innerHTML = 'Файл не выбран';
        }
    });

    // Анимация загрузки для кнопок отправки форм
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '⏳ Обработка...';
                submitButton.disabled = true;
                
                // Восстановление кнопки в случае ошибки
                setTimeout(() => {
                    if (submitButton.disabled) {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }
                }, 5000);
            }
        });
    });

    // Динамический поиск с автоматическим обновлением без потери фокуса
    const searchInput = document.querySelector('input[name="search"]');
    const categorySelect = document.querySelector('select[name="category"]');
    
    function performSearch() {
        const contactsList = document.getElementById('contactsList');
        const searchInfo = document.getElementById('searchInfo');
        const searchInfoText = document.getElementById('searchInfoText');
        
        const searchValue = searchInput ? searchInput.value : '';
        const categoryValue = categorySelect ? categorySelect.value : '';
        
        // Показываем индикатор загрузки
        if (contactsList) {
            contactsList.style.opacity = '0.6';
            contactsList.style.transition = 'opacity 0.2s';
        }
        
        // Создаем URL для поиска
        const url = new URL(window.location.href);
        if (searchValue) {
            url.searchParams.set('search', searchValue);
        } else {
            url.searchParams.delete('search');
        }
        if (categoryValue) {
            url.searchParams.set('category', categoryValue);
        } else {
            url.searchParams.delete('category');
        }
        url.searchParams.set('page', '1'); // Сбрасываем на первую страницу
        
        // Выполняем запрос через fetch
        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Парсим HTML ответа
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContactsList = doc.getElementById('contactsList');
            const newSearchInfo = doc.querySelector('.search-info');
            
            // Обновляем список контактов
            if (newContactsList && contactsList) {
                contactsList.innerHTML = newContactsList.innerHTML;
                contactsList.style.opacity = '1';
            }
            
            // Обновляем информацию о результатах
            if (searchInfo && searchInfoText) {
                if (newSearchInfo && newSearchInfo.textContent.trim()) {
                    searchInfoText.textContent = newSearchInfo.textContent.trim();
                    searchInfo.style.display = 'block';
                } else {
                    searchInfo.style.display = 'none';
                }
            }
            
            // Обновляем URL без перезагрузки страницы
            window.history.pushState({search: searchValue, category: categoryValue}, '', url.toString());
        })
        .catch(error => {
            console.error('Ошибка поиска:', error);
            if (contactsList) {
                contactsList.style.opacity = '1';
            }
        });
    }
    
    if (searchInput) {
        let searchTimeout;
        const form = searchInput.closest('form');
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            // Сохраняем позицию курсора
            const cursorPosition = this.selectionStart;
            const isFocused = document.activeElement === this;
            
            searchTimeout = setTimeout(() => {
                performSearch();
                
                // Восстанавливаем фокус и позицию курсора
                if (isFocused) {
                    searchInput.focus();
                    // Восстанавливаем позицию курсора
                    setTimeout(() => {
                        const newPosition = Math.min(cursorPosition, searchInput.value.length);
                        searchInput.setSelectionRange(newPosition, newPosition);
                    }, 0);
                }
            }, 400); // Задержка для уменьшения количества запросов
        });
        
        // Предотвращаем отправку формы по Enter, чтобы не терять фокус
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                performSearch();
            });
        }
    }
    
    // Обработка изменения категории
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            performSearch();
        });
    }
    
    // Обработка кнопок назад/вперед браузера
    window.addEventListener('popstate', function(e) {
        location.reload();
    });

    // Подсветка обязательных полей
    requiredFields.forEach(field => {
        const label = field.parentNode.querySelector('label');
        if (label) {
            if (!label.innerHTML.includes('*')) {
                label.innerHTML += ' <span style="color: #dc3545;">*</span>';
            }
        }
    });

    // Улучшение доступности
    document.addEventListener('keydown', function(e) {
        // Закрытие сообщений по ESC
        if (e.key === 'Escape') {
            const messages = document.querySelectorAll('.alert-message');
            messages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            });
        }
        
        // Навигация по формам с Tab
        if (e.key === 'Tab') {
            const focused = document.activeElement;
            if (focused.classList.contains('invalid')) {
                focused.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Добавление новой категории
    const addCategoryBtn = document.getElementById('add_category_btn');
    const newCategoryInput = document.getElementById('new_category_name');
    const categoriesList = document.getElementById('categories_list');
    
    if (addCategoryBtn && newCategoryInput && categoriesList) {
        function addCategory() {
            const categoryName = newCategoryInput.value.trim();
            
            if (!categoryName) {
                alert('Введите название категории');
                return;
            }
            
            // Отключаем кнопку на время запроса
            addCategoryBtn.disabled = true;
            addCategoryBtn.textContent = '⏳ Добавление...';
            
            fetch('add_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name: categoryName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Добавляем новую категорию в список
                    const newCategory = document.createElement('label');
                    newCategory.className = 'category-checkbox';
                    newCategory.innerHTML = `
                        <input type="checkbox" name="categories[]" value="${data.category.id}" checked>
                        <span class="checkmark"></span>
                        ${escapeHtml(data.category.name)}
                    `;
                    categoriesList.appendChild(newCategory);
                    
                    // Очищаем поле ввода
                    newCategoryInput.value = '';
                    
                    // Показываем сообщение об успехе
                    const successMsg = document.createElement('div');
                    successMsg.className = 'alert-message success';
                    successMsg.style.cssText = 'margin-top: 10px; padding: 10px; font-size: 14px;';
                    successMsg.textContent = `Категория "${data.category.name}" успешно добавлена!`;
                    addCategoryBtn.parentElement.appendChild(successMsg);
                    
                    setTimeout(() => {
                        successMsg.remove();
                    }, 3000);
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при добавлении категории');
            })
            .finally(() => {
                addCategoryBtn.disabled = false;
                addCategoryBtn.textContent = '➕ Добавить категорию';
            });
        }
        
        addCategoryBtn.addEventListener('click', addCategory);
        
        newCategoryInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCategory();
            }
        });
    }
    
    // Функция для экранирования HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Обработчик клика на категорию для переключения checkbox
    document.addEventListener('click', function(e) {
        const categoryCheckbox = e.target.closest('.category-checkbox');
        if (categoryCheckbox && !e.target.closest('input[type="checkbox"]')) {
            const checkbox = categoryCheckbox.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                // Триггерим событие change для обновления стилей
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });

    console.log('Contact Manager initialized successfully!');
});