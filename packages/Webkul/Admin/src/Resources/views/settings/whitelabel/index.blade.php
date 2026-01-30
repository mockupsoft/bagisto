<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.whitelabel.index.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.settings.whitelabel.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.whitelabel.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.settings.whitelabel.index.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('admin::app.settings.whitelabel.index.description')
            </p>
        </div>

        <div class="mt-6 grid grid-cols-[1fr_2fr] gap-10 max-xl:flex-wrap">
            <!-- General Settings -->
            <div class="grid content-start gap-2.5">
                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                    @lang('admin::app.settings.whitelabel.branding.general.title')
                </p>

                <p class="leading-[140%] text-gray-600 dark:text-gray-300">
                    @lang('admin::app.settings.whitelabel.branding.general.info')
                </p>
            </div>

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.configuration.index.whitelabel.branding.general.app-name')
                        <span class="text-red-600">*</span>
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="whitelabel[branding][general][app_name]"
                        value="{{ core()->getConfigData('whitelabel.branding.general.app_name') ?: config('app.name') }}"
                        rules="required|max:100"
                        :label="trans('admin::app.configuration.index.whitelabel.branding.general.app-name')"
                    />

                    <x-admin::form.control-group.error
                        control-name="whitelabel.branding.general.app_name"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.configuration.index.whitelabel.branding.general.company-name')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="whitelabel[branding][general][company_name]"
                        value="{{ core()->getConfigData('whitelabel.branding.general.company_name') }}"
                        rules="max:200"
                        :label="trans('admin::app.configuration.index.whitelabel.branding.general.company-name')"
                    />

                    <x-admin::form.control-group.error
                        control-name="whitelabel.branding.general.company_name"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.configuration.index.whitelabel.branding.general.company-url')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="whitelabel[branding][general][company_url]"
                        value="{{ core()->getConfigData('whitelabel.branding.general.company_url') }}"
                        rules="max:255|url"
                        :label="trans('admin::app.configuration.index.whitelabel.branding.general.company-url')"
                    />

                    <x-admin::form.control-group.error
                        control-name="whitelabel.branding.general.company_url"
                    />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.configuration.index.whitelabel.branding.general.meta-generator')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="whitelabel[branding][general][meta_generator]"
                        value="{{ core()->getConfigData('whitelabel.branding.general.meta_generator') }}"
                        rules="max:100"
                        :label="trans('admin::app.configuration.index.whitelabel.branding.general.meta-generator')"
                    />

                    <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.general.meta-generator-info')
                    </p>

                    <x-admin::form.control-group.error
                        control-name="whitelabel.branding.general.meta_generator"
                    />
                </x-admin::form.control-group>
            </div>

            <!-- Footer Settings -->
            <div class="grid content-start gap-2.5">
                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                    @lang('admin::app.settings.whitelabel.branding.footer.title')
                </p>

                <p class="leading-[140%] text-gray-600 dark:text-gray-300">
                    @lang('admin::app.settings.whitelabel.branding.footer.info')
                </p>
            </div>

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.configuration.index.whitelabel.branding.footer.show-powered-by')
                    </x-admin::form.control-group.label>

                    <input
                        type="hidden"
                        name="whitelabel[branding][footer][show_powered_by]"
                        value="0"
                    />

                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            type="checkbox"
                            name="whitelabel[branding][footer][show_powered_by]"
                            value="1"
                            class="peer sr-only"
                            {{ core()->getConfigData('whitelabel.branding.footer.show_powered_by') !== false ? 'checked' : '' }}
                        >

                        <div class="peer h-5 w-9 cursor-pointer rounded-full bg-gray-200 after:absolute after:top-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-blue-300 dark:bg-gray-800 dark:after:border-white dark:after:bg-white dark:peer-checked:bg-gray-950 after:ltr:left-0.5 peer-checked:after:ltr:translate-x-full after:rtl:right-0.5 peer-checked:after:rtl:-translate-x-full"></div>
                    </label>

                    <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.footer.show-powered-by-info')
                    </p>
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('admin::app.configuration.index.whitelabel.branding.footer.powered-by-text')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="whitelabel[branding][footer][powered_by_text]"
                        rules="max:500"
                        :label="trans('admin::app.configuration.index.whitelabel.branding.footer.powered-by-text')"
                        class="text-gray-600 dark:text-gray-300"
                    >{{ core()->getConfigData('whitelabel.branding.footer.powered_by_text') }}</x-admin::form.control-group.control>

                    <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.footer.powered-by-text-info')
                    </p>

                    <x-admin::form.control-group.error
                        control-name="whitelabel.branding.footer.powered_by_text"
                    />
                </x-admin::form.control-group>
            </div>

            <!-- Logo Settings -->
            <div class="grid content-start gap-2.5">
                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                    @lang('admin::app.configuration.index.whitelabel.branding.logos.title')
                </p>

                <p class="leading-[140%] text-gray-600 dark:text-gray-300">
                    @lang('admin::app.configuration.index.whitelabel.branding.logos.info')
                </p>
            </div>

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-6">
                    <p class="mb-2 text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.logos.admin.title')
                    </p>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.logos.admin.title-info')
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.configuration.index.whitelabel.branding.logos.admin.logo-light')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="file"
                            name="whitelabel[branding][logos][admin_logo_light]"
                            rules="image|mimes:jpeg,jpg,png,svg,webp|max:2048"
                            :label="trans('admin::app.configuration.index.whitelabel.branding.logos.admin.logo-light')"
                            accept="image/*"
                        />

                        @if ($adminLogoLight = core()->getConfigData('whitelabel.branding.logos.admin_logo_light'))
                            <div class="mt-2">
                                <img src="{{ asset($adminLogoLight) }}" alt="Admin Logo Light" class="h-16 w-auto" onerror="this.style.display='none'">
                            </div>
                        @endif

                        <x-admin::form.control-group.error
                            control-name="whitelabel.branding.logos.admin_logo_light"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.configuration.index.whitelabel.branding.logos.admin.logo-dark')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="file"
                            name="whitelabel[branding][logos][admin_logo_dark]"
                            rules="image|mimes:jpeg,jpg,png,svg,webp|max:2048"
                            :label="trans('admin::app.configuration.index.whitelabel.branding.logos.admin.logo-dark')"
                            accept="image/*"
                        />

                        @if ($adminLogoDark = core()->getConfigData('whitelabel.branding.logos.admin_logo_dark'))
                            <div class="mt-2">
                                <img src="{{ asset($adminLogoDark) }}" alt="Admin Logo Dark" class="h-16 w-auto" onerror="this.style.display='none'">
                            </div>
                        @endif

                        <x-admin::form.control-group.error
                            control-name="whitelabel.branding.logos.admin_logo_dark"
                        />
                    </x-admin::form.control-group>
                </div>

                <div>
                    <p class="mb-2 text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.logos.shop.title')
                    </p>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                        @lang('admin::app.configuration.index.whitelabel.branding.logos.shop.title-info')
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.configuration.index.whitelabel.branding.logos.shop.logo-light')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="file"
                            name="whitelabel[branding][logos][shop_logo_light]"
                            rules="image|mimes:jpeg,jpg,png,svg,webp|max:2048"
                            :label="trans('admin::app.configuration.index.whitelabel.branding.logos.shop.logo-light')"
                            accept="image/*"
                        />

                        @if ($shopLogoLight = core()->getConfigData('whitelabel.branding.logos.shop_logo_light'))
                            <div class="mt-2">
                                <img src="{{ asset($shopLogoLight) }}" alt="Shop Logo Light" class="h-16 w-auto" onerror="this.style.display='none'">
                            </div>
                        @endif

                        <x-admin::form.control-group.error
                            control-name="whitelabel.branding.logos.shop_logo_light"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.configuration.index.whitelabel.branding.logos.shop.logo-dark')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="file"
                            name="whitelabel[branding][logos][shop_logo_dark]"
                            rules="image|mimes:jpeg,jpg,png,svg,webp|max:2048"
                            :label="trans('admin::app.configuration.index.whitelabel.branding.logos.shop.logo-dark')"
                            accept="image/*"
                        />

                        @if ($shopLogoDark = core()->getConfigData('whitelabel.branding.logos.shop_logo_dark'))
                            <div class="mt-2">
                                <img src="{{ asset($shopLogoDark) }}" alt="Shop Logo Dark" class="h-16 w-auto" onerror="this.style.display='none'">
                            </div>
                        @endif

                        <x-admin::form.control-group.error
                            control-name="whitelabel.branding.logos.shop_logo_dark"
                        />
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
