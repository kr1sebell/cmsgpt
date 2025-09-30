(function () {
    'use strict';

    function formatBytes(bytes) {
        if (!bytes && bytes !== 0) {
            return '';
        }
        var units = ['Б', 'КБ', 'МБ', 'ГБ'];
        var size = bytes;
        var unitIndex = 0;
        while (size >= 1024 && unitIndex < units.length - 1) {
            size = size / 1024;
            unitIndex += 1;
        }
        return size.toFixed(unitIndex === 0 ? 0 : 1) + ' ' + units[unitIndex];
    }

    function formatDate(timestamp) {
        if (!timestamp) {
            return '';
        }
        try {
            var date = new Date(timestamp * 1000);
            return date.toLocaleString();
        } catch (e) {
            return '';
        }
    }

    function AdminMediaManager(options) {
        this.csrfToken = options.csrfToken || '';
        this.listUrl = options.listUrl;
        this.uploadUrl = options.uploadUrl;
        this.overlay = null;
        this.container = null;
        this.listElement = null;
        this.statusElement = null;
        this.filterSelect = null;
        this.uploadInput = null;
        this.uploadButton = null;
        this.selectionCallback = null;
        this.currentType = 'all';
        this.isLoading = false;
        this.render();
    }

    AdminMediaManager.prototype.render = function () {
        if (this.overlay) {
            return;
        }

        var overlay = document.createElement('div');
        overlay.className = 'media-manager-overlay';

        var container = document.createElement('div');
        container.className = 'media-manager';
        overlay.appendChild(container);

        var header = document.createElement('div');
        header.className = 'media-manager__header';
        container.appendChild(header);

        var title = document.createElement('h3');
        title.textContent = 'Медиафайлы';
        header.appendChild(title);

        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'media-manager__close';
        closeButton.setAttribute('aria-label', 'Закрыть');
        closeButton.innerHTML = '&times;';
        header.appendChild(closeButton);

        var controls = document.createElement('div');
        controls.className = 'media-manager__controls';
        container.appendChild(controls);

        var filterLabel = document.createElement('label');
        filterLabel.className = 'media-manager__filter-label';
        filterLabel.textContent = 'Тип файла:';
        controls.appendChild(filterLabel);

        var filterSelect = document.createElement('select');
        filterSelect.className = 'media-manager__filter';
        ['all', 'image', 'media', 'file'].forEach(function (value) {
            var option = document.createElement('option');
            option.value = value;
            var text = 'Все';
            if (value === 'image') {
                text = 'Изображения';
            } else if (value === 'media') {
                text = 'Видео';
            } else if (value === 'file') {
                text = 'Файлы';
            }
            option.textContent = text;
            filterSelect.appendChild(option);
        });
        controls.appendChild(filterSelect);

        var uploadWrapper = document.createElement('div');
        uploadWrapper.className = 'media-manager__upload';
        controls.appendChild(uploadWrapper);

        var uploadInput = document.createElement('input');
        uploadInput.type = 'file';
        uploadInput.className = 'media-manager__upload-input';
        uploadWrapper.appendChild(uploadInput);

        var uploadButton = document.createElement('button');
        uploadButton.type = 'button';
        uploadButton.className = 'btn media-manager__upload-btn';
        uploadButton.textContent = 'Загрузить';
        uploadButton.disabled = true;
        uploadWrapper.appendChild(uploadButton);

        var statusElement = document.createElement('div');
        statusElement.className = 'media-manager__status';
        container.appendChild(statusElement);

        var listElement = document.createElement('div');
        listElement.className = 'media-manager__list';
        container.appendChild(listElement);

        document.body.appendChild(overlay);

        this.overlay = overlay;
        this.container = container;
        this.listElement = listElement;
        this.statusElement = statusElement;
        this.filterSelect = filterSelect;
        this.uploadInput = uploadInput;
        this.uploadButton = uploadButton;

        this.bindEvents();
    };

    AdminMediaManager.prototype.bindEvents = function () {
        var _this = this;
        this.overlay.addEventListener('click', function (event) {
            if (event.target === _this.overlay) {
                _this.close();
            }
        });
        this.overlay.querySelector('.media-manager__close').addEventListener('click', function () {
            _this.close();
        });
        this.filterSelect.addEventListener('change', function () {
            _this.load(_this.filterSelect.value);
        });
        this.uploadInput.addEventListener('change', function () {
            _this.uploadButton.disabled = !_this.uploadInput.files.length;
        });
        this.uploadButton.addEventListener('click', function () {
            if (!_this.uploadInput.files.length || _this.isLoading) {
                return;
            }
            _this.upload(_this.uploadInput.files[0]);
        });
    };

    AdminMediaManager.prototype.open = function (type, callback) {
        this.selectionCallback = callback;
        this.overlay.classList.add('is-open');
        var normalizedType = 'all';
        if (type === 'image' || type === 'media' || type === 'file') {
            normalizedType = type;
        }
        this.filterSelect.value = normalizedType;
        this.load(normalizedType);
    };

    AdminMediaManager.prototype.close = function () {
        this.overlay.classList.remove('is-open');
        this.selectionCallback = null;
        this.statusElement.textContent = '';
        this.listElement.innerHTML = '';
        this.uploadInput.value = '';
        this.uploadButton.disabled = true;
    };

    AdminMediaManager.prototype.load = function (type) {
        var _this = this;
        if (this.isLoading) {
            return;
        }
        this.isLoading = true;
        this.currentType = type;
        this.statusElement.textContent = 'Загрузка...';
        this.listElement.innerHTML = '';

        var url = new URL(this.listUrl, window.location.origin);
        url.searchParams.set('type', type);
        if (this.csrfToken) {
            url.searchParams.set('csrf_token', this.csrfToken);
        }

        fetch(url.toString(), {
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Ошибка загрузки');
            }
            return response.json();
        }).then(function (data) {
            var files = (data && data.files) ? data.files : [];
            _this.renderList(files);
        }).catch(function (error) {
            console.error(error);
            _this.statusElement.textContent = 'Не удалось получить список файлов.';
        }).finally(function () {
            _this.isLoading = false;
        });
    };

    AdminMediaManager.prototype.renderList = function (files) {
        var _this = this;
        this.listElement.innerHTML = '';
        if (!files.length) {
            this.statusElement.textContent = 'Файлы не найдены.';
            return;
        }
        this.statusElement.textContent = '';

        files.forEach(function (file) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'media-manager__item';

            var preview = document.createElement('div');
            preview.className = 'media-manager__preview';
            if (file.type === 'image') {
                var image = document.createElement('img');
                image.src = file.url;
                image.alt = file.name;
                preview.appendChild(image);
            } else {
                var badge = document.createElement('span');
                badge.className = 'media-manager__badge';
                badge.textContent = file.type === 'media' ? 'Видео' : 'Файл';
                preview.appendChild(badge);
            }
            button.appendChild(preview);

            var name = document.createElement('div');
            name.className = 'media-manager__name';
            name.textContent = file.name;
            button.appendChild(name);

            var meta = document.createElement('div');
            meta.className = 'media-manager__meta';
            var size = formatBytes(file.size);
            var modified = formatDate(file.modified);
            meta.textContent = [size, modified].filter(Boolean).join(' · ');
            button.appendChild(meta);

            button.addEventListener('click', function () {
                _this.select(file);
            });

            _this.listElement.appendChild(button);
        });
    };

    AdminMediaManager.prototype.select = function (file) {
        if (typeof this.selectionCallback === 'function') {
            var metadata = {};
            if (file.type === 'image') {
                metadata.alt = file.name;
            } else {
                metadata.text = file.name;
            }
            this.selectionCallback(file.url, metadata);
        }
        this.close();
    };

    AdminMediaManager.prototype.upload = function (file) {
        var _this = this;
        this.isLoading = true;
        this.uploadButton.disabled = true;
        this.uploadButton.textContent = 'Загрузка...';
        this.statusElement.textContent = '';

        var formData = new FormData();
        formData.append('file', file);
        if (this.csrfToken) {
            formData.append('csrf_token', this.csrfToken);
        }

        fetch(this.uploadUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) {
                throw response;
            }
            return response.json();
        }).then(function (data) {
            if (!data || !data.location) {
                throw new Error(data && data.error ? data.error : 'Не удалось загрузить файл');
            }
            _this.load(_this.currentType);
            if (typeof _this.selectionCallback === 'function') {
                var metadata = data.type === 'image' ? { alt: data.name } : { text: data.name };
                _this.selectionCallback(data.location, metadata);
                _this.close();
            }
        }).catch(function (error) {
            if (error && typeof error.json === 'function') {
                error.json().then(function (payload) {
                    _this.statusElement.textContent = payload && payload.error ? payload.error : 'Ошибка загрузки файла.';
                }).catch(function () {
                    _this.statusElement.textContent = 'Ошибка загрузки файла.';
                });
            } else if (error && error.message) {
                _this.statusElement.textContent = error.message;
            } else {
                _this.statusElement.textContent = 'Ошибка загрузки файла.';
            }
        }).finally(function () {
            _this.isLoading = false;
            _this.uploadButton.disabled = false;
            _this.uploadButton.textContent = 'Загрузить';
            _this.uploadInput.value = '';
        });
    };

    function initEditor() {
        if (typeof tinymce === 'undefined') {
            return;
        }
        var textarea = document.getElementById('body');
        if (!textarea) {
            return;
        }
        var form = textarea.closest('form');
        var csrfInput = form ? form.querySelector('input[name="csrf_token"]') : null;
        var csrfToken = csrfInput ? csrfInput.value : '';
        var uploadUrl = textarea.getAttribute('data-upload-url');
        var listUrl = textarea.getAttribute('data-list-url');
        var mediaManager = new AdminMediaManager({
            csrfToken: csrfToken,
            listUrl: listUrl,
            uploadUrl: uploadUrl
        });

        tinymce.init({
            selector: '#body',
            language: 'ru',
            height: 600,
            branding: false,
            contextmenu: 'link image table',
            plugins: 'link image media table lists advlist code fullscreen autoresize',
            menubar: 'file edit view insert format tools table',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | table | removeformat | code fullscreen',
            convert_urls: false,
            file_picker_types: 'file image media',
            automatic_uploads: true,
            images_upload_handler: function (blobInfo, progress) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.withCredentials = true;
                    xhr.open('POST', uploadUrl);
                    xhr.onload = function () {
                        if (xhr.status !== 200) {
                            reject('Ошибка загрузки изображения');
                            return;
                        }
                        var response;
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (e) {
                            reject('Неверный ответ сервера');
                            return;
                        }
                        if (!response || !response.location) {
                            reject(response && response.error ? response.error : 'Не удалось загрузить изображение');
                            return;
                        }
                        resolve(response.location);
                    };
                    xhr.onerror = function () {
                        reject('Ошибка сети');
                    };
                    xhr.upload.onprogress = function (event) {
                        if (event.lengthComputable) {
                            progress(event.loaded / event.total * 100);
                        }
                    };
                    var formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    if (csrfToken) {
                        formData.append('csrf_token', csrfToken);
                    }
                    xhr.send(formData);
                });
            },
            file_picker_callback: function (callback, value, meta) {
                mediaManager.open(meta.filetype, function (url, options) {
                    callback(url, options);
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initEditor);
})();
