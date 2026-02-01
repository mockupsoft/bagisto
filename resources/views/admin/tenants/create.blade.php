<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.tenants.create.title')
    </x-slot>

    <x-admin::form
        action="{{ route('admin.tenants.store') }}"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.tenants.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.tenants.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button 
                    type="submit" 
                    class="primary-button"
                >
                    @lang('admin::app.tenants.create.save-btn')
                </button>
            </div>
        </div>

        <!-- body content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- Basic Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.tenants.create.basic-information')
                    </p>

                    <!-- Tenant Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.tenants.create.fields.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="name"
                            name="name"
                            rules="required|max:255"
                            :value="old('name')"
                            :label="trans('admin::app.tenants.create.fields.name')"
                            :placeholder="trans('admin::app.tenants.create.fields.name-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Tenant Slug -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.tenants.create.fields.slug')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="slug"
                            name="slug"
                            rules="required|max:255"
                            :value="old('slug')"
                            :label="trans('admin::app.tenants.create.fields.slug')"
                            :placeholder="trans('admin::app.tenants.create.fields.slug-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="slug" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.slug-info')
                        </p>
                    </x-admin::form.control-group>

                    <!-- Store Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.store_name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="store_name"
                            name="store_name"
                            :value="old('store_name')"
                            :label="trans('admin::app.tenants.create.fields.store_name')"
                            :placeholder="trans('admin::app.tenants.create.fields.store_name-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="store_name" />
                    </x-admin::form.control-group>
                </div>

                <!-- Domain Settings -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.tenants.create.domain-settings')
                    </p>

                    <!-- Primary Domain -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.primary_domain')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="primary_domain"
                            name="primary_domain"
                            :value="old('primary_domain')"
                            :label="trans('admin::app.tenants.create.fields.primary_domain')"
                            :placeholder="trans('admin::app.tenants.create.fields.primary_domain-placeholder', ['base' => config('saas.base_domain', 'example.test')])"
                        />

                        <x-admin::form.control-group.error control-name="primary_domain" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.primary_domain-info')
                        </p>
                    </x-admin::form.control-group>
                </div>

                <!-- Database Settings -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.tenants.create.database-settings')
                    </p>

                    <!-- DB Host -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.db_host')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="db_host"
                            name="db_host"
                            :value="old('db_host', config('saas.tenant_db.host', '127.0.0.1'))"
                            :label="trans('admin::app.tenants.create.fields.db_host')"
                            :placeholder="trans('admin::app.tenants.create.fields.db_host-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="db_host" />
                    </x-admin::form.control-group>

                    <!-- DB Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.db_name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="db_name"
                            name="db_name"
                            :value="old('db_name')"
                            :label="trans('admin::app.tenants.create.fields.db_name')"
                            :placeholder="trans('admin::app.tenants.create.fields.db_name-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="db_name" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.db_name-info')
                        </p>
                    </x-admin::form.control-group>

                    <!-- DB Username -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.db_username')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="db_username"
                            name="db_username"
                            :value="old('db_username')"
                            :label="trans('admin::app.tenants.create.fields.db_username')"
                            :placeholder="trans('admin::app.tenants.create.fields.db_username-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="db_username" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.db_username-info-auto')
                        </p>
                    </x-admin::form.control-group>

                    <!-- DB Password -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.db_password')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="password"
                            id="db_password"
                            name="db_password"
                            :value="old('db_password')"
                            :label="trans('admin::app.tenants.create.fields.db_password')"
                            :placeholder="trans('admin::app.tenants.create.fields.db_password-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="db_password" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.db_password-info')
                        </p>
                    </x-admin::form.control-group>
                </div>

                <!-- Admin User -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.tenants.create.admin-user')
                    </p>

                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.tenants.create.fields.admin-auto-info')
                    </p>

                    <!-- Admin Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.admin_name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="admin_name"
                            name="admin_name"
                            :value="old('admin_name')"
                            :label="trans('admin::app.tenants.create.fields.admin_name')"
                            :placeholder="trans('admin::app.tenants.create.fields.admin_name-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="admin_name" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.admin_name-info')
                        </p>
                    </x-admin::form.control-group>

                    <!-- Admin Email -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.admin_email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            id="admin_email"
                            name="admin_email"
                            :value="old('admin_email')"
                            :label="trans('admin::app.tenants.create.fields.admin_email')"
                            :placeholder="trans('admin::app.tenants.create.fields.admin_email-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="admin_email" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.admin_email-info')
                        </p>
                    </x-admin::form.control-group>

                    <!-- Admin Password -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.admin_password')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="password"
                            id="admin_password"
                            name="admin_password"
                            :value="old('admin_password')"
                            :label="trans('admin::app.tenants.create.fields.admin_password')"
                            :placeholder="trans('admin::app.tenants.create.fields.admin_password-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="admin_password" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.admin_password-info')
                        </p>
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Right sub-component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <!-- Provisioning Settings -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.tenants.create.provisioning-settings')
                    </p>

                    <!-- Provision Now -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.tenants.create.fields.provision_now')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            id="provision_now"
                            name="provision_now"
                            :value="1"
                            :checked="old('provision_now', true)"
                        />

                        <x-admin::form.control-group.error control-name="provision_now" />
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.tenants.create.fields.provision_now-info')
                        </p>
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
