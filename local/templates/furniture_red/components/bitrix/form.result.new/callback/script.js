(function () {
    'use strict';

    function boot() {
        document.querySelectorAll('[data-callback-form]').forEach(function (root) {
            if (root.dataset.callbackFormMounted === 'yes') {
                return;
            }

            var config;
            try {
                config = JSON.parse(root.dataset.callbackForm);
            } catch (error) {
                return;
            }

            root.dataset.callbackFormMounted = 'yes';

            BX.Vue3.BitrixVue.createApp({
                data: function () {
                    var values = {};
                    config.fields.forEach(function (field) {
                        values[field.key] = field.type === 'checkbox' ? false : '';
                    });

                    return {
                        config: config,
                        values: values,
                        errors: {},
                        isOpen: false,
                        isSending: false,
                        successMessage: '',
                        commonError: '',
                        autoOpenTimer: null,
                        previousFocus: null
                    };
                },
                mounted: function () {
                    document.addEventListener('keydown', this.onKeydown);

                    if (!sessionStorage.getItem(this.config.storageKey)) {
                        this.autoOpenTimer = window.setTimeout(function () {
                            sessionStorage.setItem(this.config.storageKey, 'yes');
                            this.open();
                        }.bind(this), this.config.delay);
                    }
                },
                beforeUnmount: function () {
                    window.clearTimeout(this.autoOpenTimer);
                    document.removeEventListener('keydown', this.onKeydown);
                    document.body.classList.remove('callback-form-lock');
                },
                methods: {
                    open: function () {
                        this.previousFocus = document.activeElement;
                        this.isOpen = true;
                        document.body.classList.add('callback-form-lock');
                        this.$nextTick(function () {
                            var firstInput = this.$refs.dialog.querySelector('input, textarea, select');
                            if (firstInput) {
                                firstInput.focus();
                            }
                        });
                    },
                    close: function () {
                        if (this.isSending) {
                            return;
                        }
                        this.isOpen = false;
                        document.body.classList.remove('callback-form-lock');
                        if (this.previousFocus && typeof this.previousFocus.focus === 'function') {
                            this.previousFocus.focus();
                        }
                    },
                    onKeydown: function (event) {
                        if (event.key === 'Escape' && this.isOpen) {
                            this.close();
                        }
                    },
                    validate: function () {
                        var errors = {};

                        this.config.fields.forEach(function (field) {
                            var value = this.values[field.key];
                            var isEmpty = field.type === 'checkbox' ? !value : !String(value || '').trim();

                            if (field.required && isEmpty) {
                                errors[field.sid] = 'Поле обязательно для заполнения.';
                                return;
                            }

                            if (field.validation === 'phone' && value) {
                                var digits = String(value).replace(/\D/g, '');
                                if (digits.length === 10) {
                                    digits = '7' + digits;
                                } else if (digits.length === 11 && digits.charAt(0) === '8') {
                                    digits = '7' + digits.slice(1);
                                }

                                if (!/^7[3489]\d{9}$/.test(digits)) {
                                    errors[field.sid] = 'Введите корректный номер телефона.';
                                }
                            }
                        }, this);

                        this.errors = errors;
                        return Object.keys(errors).length === 0;
                    },
                    fieldError: function (field) {
                        return this.errors[field.sid] || '';
                    },
                    submit: async function () {
                        this.commonError = '';
                        this.successMessage = '';

                        if (!this.validate()) {
                            this.$nextTick(function () {
                                var invalid = this.$refs.dialog.querySelector('[aria-invalid="true"]');
                                if (invalid) {
                                    invalid.focus();
                                }
                            });
                            return;
                        }

                        var body = new FormData();
                        body.append('sessid', this.config.sessid);
                        body.append('form_sid', this.config.formSid);
                        body.append('WEB_FORM_ID', String(this.config.formId));

                        this.config.fields.forEach(function (field) {
                            var value = this.values[field.key];
                            if (field.type === 'checkbox') {
                                if (value) {
                                    body.append(field.name, field.answerValue);
                                }
                            } else {
                                body.append(field.name, value == null ? '' : String(value));
                            }
                        }, this);

                        this.isSending = true;
                        try {
                            var response = await fetch(this.config.endpoint, {
                                method: 'POST',
                                body: body,
                                credentials: 'same-origin',
                                headers: {'X-Requested-With': 'XMLHttpRequest'}
                            });
                            var result = await response.json();

                            if (!response.ok || !result.success) {
                                this.errors = result.errors || {};
                                this.commonError = result.message || 'Не удалось отправить форму.';
                                return;
                            }

                            this.successMessage = result.message;
                            this.errors = {};
                            this.config.fields.forEach(function (field) {
                                this.values[field.key] = field.type === 'checkbox' ? false : '';
                            }, this);
                        } catch (error) {
                            this.commonError = 'Ошибка соединения. Проверьте интернет и попробуйте снова.';
                        } finally {
                            this.isSending = false;
                        }
                    }
                },
                template: `
                    <div v-if="isOpen" class="callback-form-overlay" @mousedown.self="close">
                        <section ref="dialog" class="callback-form-modal" role="dialog" aria-modal="true" :aria-labelledby="'callback-form-title-' + config.formId">
                            <button class="callback-form-close" type="button" @click="close" aria-label="Закрыть окно">×</button>
                            <template v-if="!successMessage">
                                <h2 :id="'callback-form-title-' + config.formId">{{ config.title }}</h2>
                                <p v-if="config.description" class="callback-form-description">{{ config.description }}</p>

                                <form @submit.prevent="submit" novalidate>
                                    <div v-for="field in config.fields" :key="field.key" class="callback-form-field" :class="{'callback-form-field--checkbox': field.type === 'checkbox'}">
                                        <template v-if="field.type === 'checkbox'">
                                            <label class="callback-form-checkbox">
                                                <input v-model="values[field.key]" type="checkbox" :name="field.name" :aria-invalid="Boolean(fieldError(field))" :aria-describedby="fieldError(field) ? 'callback-error-' + field.key : null">
                                                <span>{{ field.label }}<b v-if="field.required" aria-hidden="true"> *</b></span>
                                            </label>
                                        </template>

                                        <template v-else>
                                            <label :for="'callback-field-' + field.key">{{ field.label }}<b v-if="field.required" aria-hidden="true"> *</b></label>
                                            <textarea v-if="field.type === 'textarea'" v-model="values[field.key]" :id="'callback-field-' + field.key" :name="field.name" rows="4" :required="field.required" :aria-invalid="Boolean(fieldError(field))" :aria-describedby="fieldError(field) ? 'callback-error-' + field.key : null"></textarea>
                                            <select v-else-if="field.type === 'dropdown' || field.type === 'multiselect'" v-model="values[field.key]" :id="'callback-field-' + field.key" :name="field.name" :required="field.required" :multiple="field.type === 'multiselect'" :aria-invalid="Boolean(fieldError(field))">
                                                <option value="">Выберите вариант</option>
                                                <option v-for="option in field.options" :key="option.value" :value="option.value">{{ option.label }}</option>
                                            </select>
                                            <input v-else v-model="values[field.key]" :id="'callback-field-' + field.key" :name="field.name" :type="field.inputType" :autocomplete="field.autocomplete" :placeholder="field.placeholder" :required="field.required" :aria-invalid="Boolean(fieldError(field))" :aria-describedby="fieldError(field) ? 'callback-error-' + field.key : null">
                                        </template>

                                        <p v-if="fieldError(field)" :id="'callback-error-' + field.key" class="callback-form-error">{{ fieldError(field) }}</p>
                                    </div>

                                    <p v-if="commonError" class="callback-form-common-error" role="alert">{{ commonError }}</p>
                                    <button class="callback-form-submit" type="submit" :disabled="isSending">
                                        {{ isSending ? 'Отправляем…' : config.submitLabel }}
                                    </button>
                                </form>
                            </template>

                            <div v-else class="callback-form-success" role="status">
                                <div class="callback-form-success-icon" aria-hidden="true">✓</div>
                                <h2>Заявка отправлена</h2>
                                <p>{{ successMessage }}</p>
                                <button class="callback-form-submit" type="button" @click="close">Закрыть</button>
                            </div>
                        </section>
                    </div>
                `
            }).mount(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
