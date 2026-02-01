<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;

class WhitelabelController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CoreConfigRepository $coreConfigRepository
    ) {}

    /**
     * Display whitelabel settings page.
     */
    public function index(): View
    {
        return view('admin::settings.whitelabel.index');
    }

    /**
     * Store whitelabel settings.
     */
    public function store(): RedirectResponse
    {
        // Get locale and channel first, then exclude them from data
        $locale = request()->get('locale', core()->getRequestedLocaleCode());
        $channel = request()->get('channel', core()->getRequestedChannelCode());
        
        // Get all uploaded files first (before except removes them)
        $allFiles = request()->allFiles();
        
        // Debug: Log everything
        \Log::info('=== WHITELABEL UPLOAD DEBUG ===');
        \Log::info('All files:', ['files' => $allFiles]);
        \Log::info('Request keys:', ['keys' => array_keys(request()->all())]);
        \Log::info('Has whitelabel in files:', ['has' => isset($allFiles['whitelabel'])]);
        if (isset($allFiles['whitelabel'])) {
            \Log::info('Whitelabel structure:', ['structure' => $allFiles['whitelabel']]);
        }
        
        $data = request()->except(['_token', 'locale', 'channel']);

        // Handle logo uploads manually since CoreConfigRepository's hasFile check uses dot notation
        // but form sends nested array format
        $logoFields = [
            'admin_logo_light' => 'whitelabel.branding.logos.admin_logo_light',
            'admin_logo_dark' => 'whitelabel.branding.logos.admin_logo_dark',
            'shop_logo_light' => 'whitelabel.branding.logos.shop_logo_light',
            'shop_logo_dark' => 'whitelabel.branding.logos.shop_logo_dark',
        ];

        foreach ($logoFields as $fieldKey => $fieldName) {
            $file = null;
            
            // Method 1: Try nested array from allFiles (most common)
            if (isset($allFiles['whitelabel']['branding']['logos'][$fieldKey])) {
                $file = $allFiles['whitelabel']['branding']['logos'][$fieldKey];
                \Log::info("Found file via Method 1 for {$fieldKey}");
            }
            
            // Method 2: Try all possible keys
            $possibleKeys = [
                "whitelabel.branding.logos.{$fieldKey}",
                "whitelabel[branding][logos][{$fieldKey}]",
                "whitelabel.branding.logos.{$fieldKey}",
            ];
            
            foreach ($possibleKeys as $key) {
                if (!$file && request()->hasFile($key)) {
                    $file = request()->file($key);
                    \Log::info("Found file via key: {$key}");
                    break;
                }
            }
            
            // Method 3: Search in all files recursively
            if (!$file) {
                $file = $this->findFileInArray($allFiles, $fieldKey);
                if ($file) {
                    \Log::info("Found file via recursive search for {$fieldKey}");
                }
            }
            
            \Log::info("Final check for {$fieldKey}:", [
                'has_file' => $file !== null,
                'is_uploaded_file' => $file instanceof \Illuminate\Http\UploadedFile,
                'file_class' => $file ? get_class($file) : null
            ]);

            if ($file && $file instanceof \Illuminate\Http\UploadedFile) {
                // Delete old logo if exists
                $oldLogo = core()->getConfigData($fieldName);
                if ($oldLogo) {
                    // Try to delete from both locations
                    $oldPath = public_path('themes/admin/default/build/assets/' . basename($oldLogo));
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                    if (Storage::disk('public')->exists($oldLogo)) {
                        Storage::disk('public')->delete($oldLogo);
                    }
                }

                // Store logo to themes directory (same location as default logos)
                $themesDir = public_path('themes/admin/default/build/assets');
                if (!is_dir($themesDir)) {
                    File::makeDirectory($themesDir, 0755, true);
                }
                
                $extension = $file->getClientOriginalExtension();
                $fileName = 'whitelabel-' . $fieldKey . '-' . time() . '.' . $extension;
                $filePath = $themesDir . '/' . $fileName;
                
                // Move uploaded file to themes directory
                $file->move($themesDir, $fileName);
                
                // Save relative path for URL generation
                $path = 'themes/admin/default/build/assets/' . $fileName;
                
                \Log::info('Whitelabel logo uploaded:', [
                    'field' => $fieldName,
                    'path' => $path,
                    'full_path' => $filePath,
                    'url' => asset($path),
                    'exists' => file_exists($filePath)
                ]);
                
                // Save directly to CoreConfig model since recursiveArray doesn't handle string values
                $field = core()->getConfigField($fieldName);
                $channelBased = !empty($field['channel_based'] ?? false);
                $localeBased = !empty($field['locale_based'] ?? false);
                
                $coreConfig = CoreConfig::where('code', $fieldName)
                    ->when($channelBased, fn($q) => $q->where('channel_code', $channel))
                    ->when($localeBased, fn($q) => $q->where('locale_code', $locale))
                    ->first();
                
                if ($coreConfig) {
                    $coreConfig->update([
                        'value' => $path,
                    ]);
                } else {
                    CoreConfig::create([
                        'code' => $fieldName,
                        'value' => $path,
                        'locale_code' => $localeBased ? $locale : null,
                        'channel_code' => $channelBased ? $channel : null,
                    ]);
                }
                
                // Remove file from data array to prevent CoreConfigRepository from trying to process it
                if (isset($data['whitelabel']['branding']['logos'][$fieldKey])) {
                    unset($data['whitelabel']['branding']['logos'][$fieldKey]);
                }
            }
        }

        // CoreConfigRepository requires locale and channel keys (added separately, not in data array)
        $data['locale'] = $locale;
        $data['channel'] = $channel;

        $this->coreConfigRepository->create($data);

        session()->flash('success', trans('admin::app.settings.whitelabel.index.save-success'));

        return redirect()->back();
    }
    
    /**
     * Recursively find file in array by key name.
     */
    private function findFileInArray(array $array, string $searchKey): ?\Illuminate\Http\UploadedFile
    {
        foreach ($array as $key => $value) {
            if ($key === $searchKey && $value instanceof \Illuminate\Http\UploadedFile) {
                return $value;
            }
            
            if (is_array($value)) {
                $result = $this->findFileInArray($value, $searchKey);
                if ($result) {
                    return $result;
                }
            }
        }
        
        return null;
    }
}
