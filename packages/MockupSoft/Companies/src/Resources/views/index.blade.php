<x-admin::layouts>
    <x-slot:title>
        @lang('mockupsoft-companies::app.companies.title')
    </x-slot>

    <v-companies />

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-companies-template"
        >
            <div>
                <div class="flex items-center justify-between">
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('mockupsoft-companies::app.companies.title')
                    </p>

                    <div class="flex items-center gap-x-2.5">
                        @if (bouncer()->hasPermission('mockupsoft.companies.create'))
                            <button
                                type="button"
                                class="primary-button"
                                @click="resetForm(); $refs.companyModal.open()"
                            >
                                @lang('mockupsoft-companies::app.companies.create-btn')
                            </button>
                        @endif
                    </div>
                </div>

                <x-admin::datagrid
                    src="{{ route('mockupsoft.companies.index') }}"
                    ref="datagrid"
                >
                    <template #body="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <template v-if="isLoading">
                            <x-admin::shimmer.datagrid.table.body />
                        </template>

                        <template v-else>
                            <div
                                v-for="record in available.records"
                                class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                                :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                            >
                                <p>@{{ record.id }}</p>
                                <p>@{{ record.name }}</p>
                                <p>@{{ record.email }}</p>
                                <p>@{{ record.phone || '-' }}</p>
                                <p>@{{ record.created_at }}</p>

                                <div class="flex justify-end">
                                    @if (bouncer()->hasPermission('mockupsoft.companies.edit'))
                                        <a @click="editCompany(record)">
                                            <span
                                                class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                                title="@lang('mockupsoft-companies::app.datagrid.edit')"
                                            ></span>
                                        </a>
                                    @endif

                                    @if (bouncer()->hasPermission('mockupsoft.companies.delete'))
                                        <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                            <span
                                                class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                                title="@lang('mockupsoft-companies::app.datagrid.delete')"
                                            ></span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </template>
                    </template>
                </x-admin::datagrid>

                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                    ref="modalForm"
                >
                    <form
                        @submit="handleSubmit($event, saveCompany)"
                        ref="companyForm"
                    >
                        <x-admin::modal ref="companyModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    <span v-if="isEditing">
                                        @lang('mockupsoft-companies::app.companies.edit.title')
                                    </span>
                                    <span v-else>
                                        @lang('mockupsoft-companies::app.companies.create.title')
                                    </span>
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="id"
                                    v-model="formData.id"
                                />

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('mockupsoft-companies::app.companies.form.name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        v-model="formData.name"
                                        :label="trans('mockupsoft-companies::app.companies.form.name')"
                                        :placeholder="trans('mockupsoft-companies::app.companies.form.name-placeholder')"
                                    />

                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('mockupsoft-companies::app.companies.form.email')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="email"
                                        name="email"
                                        rules="required|email"
                                        v-model="formData.email"
                                        :label="trans('mockupsoft-companies::app.companies.form.email')"
                                        :placeholder="trans('mockupsoft-companies::app.companies.form.email-placeholder')"
                                    />

                                    <x-admin::form.control-group.error control-name="email" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('mockupsoft-companies::app.companies.form.phone')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="phone"
                                        v-model="formData.phone"
                                        :label="trans('mockupsoft-companies::app.companies.form.phone')"
                                        :placeholder="trans('mockupsoft-companies::app.companies.form.phone-placeholder')"
                                    />

                                    <x-admin::form.control-group.error control-name="phone" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('mockupsoft-companies::app.companies.form.address')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        name="address"
                                        v-model="formData.address"
                                        :label="trans('mockupsoft-companies::app.companies.form.address')"
                                        :placeholder="trans('mockupsoft-companies::app.companies.form.address-placeholder')"
                                    />

                                    <x-admin::form.control-group.error control-name="address" />
                                </x-admin::form.control-group>
                            </x-slot>

                            <x-slot:footer>
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('mockupsoft-companies::app.companies.form.save-btn')"
                                    ::loading="isProcessing"
                                    ::disabled="isProcessing"
                                />
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-companies', {
                template: '#v-companies-template',

                data() {
                    return {
                        isEditing: false,
                        isProcessing: false,
                        formData: {
                            id: null,
                            name: '',
                            email: '',
                            phone: '',
                            address: '',
                        },
                    };
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },
                },

                methods: {
                    resetForm() {
                        this.isEditing = false;
                        this.formData = {
                            id: null,
                            name: '',
                            email: '',
                            phone: '',
                            address: '',
                        };
                    },

                    editCompany(record) {
                        this.isEditing = true;
                        this.formData = {
                            id: record.id,
                            name: record.name,
                            email: record.email,
                            phone: record.phone || '',
                            address: record.address || '',
                        };

                        this.$refs.companyModal.open();
                    },

                    saveCompany(params, { resetForm, setErrors }) {
                        this.isProcessing = true;

                        let formData = new FormData(this.$refs.companyForm);
                        let url = "{{ route('mockupsoft.companies.store') }}";

                        if (this.formData.id) {
                            formData.append('_method', 'PUT');
                            url = "{{ route('mockupsoft.companies.update', ':id') }}".replace(':id', this.formData.id);
                        }

                        this.$axios.post(url, formData)
                            .then((response) => {
                                this.isProcessing = false;
                                this.$refs.companyModal.close();
                                this.$refs.datagrid.get();
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                this.resetForm();
                            })
                            .catch((error) => {
                                this.isProcessing = false;

                                if (error.response.status === 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
